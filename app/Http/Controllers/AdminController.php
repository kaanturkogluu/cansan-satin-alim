<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Unit;
use App\Models\Role;
use App\Models\RequestForm;
use App\Models\RequestItem;
use App\Models\RequestHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function users()
    {
        $users = User::with('department')->paginate(10);
        $roleNames = Role::all()->keyBy('slug');
        return view('admin.users.index', compact('users', 'roleNames'));
    }

    public function createUser()
    {
        $departments = Department::all();
        $roles = Role::orderBy('sort_order')->get();
        return view('admin.users.create', compact('departments', 'roles'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->whereNull('deleted_at')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::exists('roles', 'slug')],
            'department_id' => ['required_unless:role,admin', 'nullable', 'exists:departments,id'],
        ], [
            'department_id.required_unless' => __('Department is required for all roles except Admin.'),
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'department_id' => $request->department_id,
        ]);

        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    public function editUser(User $user)
    {
        $departments = Department::all();
        $roles = Role::orderBy('sort_order')->get();
        return view('admin.users.edit', compact('user', 'departments', 'roles'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)->whereNull('deleted_at')],
            'role' => ['required', 'string', Rule::exists('roles', 'slug')],
            'department_id' => ['required_unless:role,admin', 'nullable', 'exists:departments,id'],
        ], [
            'department_id.required_unless' => __('Department is required for all roles except Admin.'),
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'department_id' => $request->department_id,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    /** Roller (Roles) */
    public function roles()
    {
        $roles = Role::orderBy('sort_order')->paginate(15);
        return view('admin.roles.index', compact('roles'));
    }

    public function createRole()
    {
        return view('admin.roles.create');
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:50|unique:roles,slug|regex:/^[a-z0-9_]+$/',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'slug.regex' => __('Slug can only contain lowercase letters, numbers and underscores.'),
        ]);
        Role::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'sort_order' => (int) ($request->sort_order ?? 0),
        ]);
        return redirect()->route('admin.roles')->with('success', __('Role created successfully.'));
    }

    public function editRole(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function updateRole(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'slug')->ignore($role->id)],
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'slug.regex' => __('Slug can only contain lowercase letters, numbers and underscores.'),
        ]);
        $oldSlug = $role->slug;
        $role->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'sort_order' => (int) ($request->sort_order ?? 0),
        ]);
        if ($oldSlug !== $role->slug) {
            User::where('role', $oldSlug)->update(['role' => $role->slug]);
        }
        return redirect()->route('admin.roles')->with('success', __('Role updated successfully.'));
    }

    public function destroyRole(Role $role)
    {
        $userCount = User::where('role', $role->slug)->count();
        if ($userCount > 0) {
            return back()->with('error', __('Cannot delete role: it is assigned to :count user(s).', ['count' => $userCount]));
        }
        $role->delete();
        return redirect()->route('admin.roles')->with('success', __('Role deleted successfully.'));
    }

    /** Bölümler (Departments) */
    public function departments()
    {
        $departments = Department::orderBy('name')->paginate(15);
        return view('admin.departments.index', compact('departments'));
    }

    public function createDepartment()
    {
        return view('admin.departments.create');
    }

    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        Department::create($request->only('name'));
        return redirect()->route('admin.departments')->with('success', __('Department created successfully.'));
    }

    public function editDepartment(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $department->update($request->only('name'));
        return redirect()->route('admin.departments')->with('success', __('Department updated successfully.'));
    }

    public function destroyDepartment(Department $department)
    {
        $userCount = User::where('department_id', $department->id)->count();
        if ($userCount > 0) {
            return back()->with('error', __('Cannot delete department: it is assigned to :count user(s).', ['count' => $userCount]));
        }
        $department->delete();
        return redirect()->route('admin.departments')->with('success', __('Department deleted successfully.'));
    }

    /** Birimler (Units) */
    public function units()
    {
        $units = Unit::orderBy('name')->paginate(15);
        return view('admin.units.index', compact('units'));
    }

    public function createUnit()
    {
        return view('admin.units.create');
    }

    public function storeUnit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'symbol' => 'nullable|string|max:20',
        ]);
        Unit::create($request->only('name', 'symbol'));
        return redirect()->route('admin.units')->with('success', __('Unit created successfully.'));
    }

    public function editUnit(Unit $unit)
    {
        return view('admin.units.edit', compact('unit'));
    }

    public function updateUnit(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'symbol' => 'nullable|string|max:20',
        ]);
        $unit->update($request->only('name', 'symbol'));
        return redirect()->route('admin.units')->with('success', __('Unit updated successfully.'));
    }

    public function destroyUnit(Unit $unit)
    {
        $unit->delete();
        return redirect()->route('admin.units')->with('success', __('Unit deleted successfully.'));
    }

    public function requests(Request $request)
    {
        $query = \App\Models\RequestForm::with(['user', 'department']);

        // Tarihe göre sıralama: en_eski | en_yeni (varsayılan)
        $dateOrder = $request->get('date_order', 'en_yeni');
        $query->orderBy('created_at', $dateOrder === 'en_eski' ? 'asc' : 'desc');

        // Durum filtresi (tümü veya tek durum)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(20)->withQueryString();
        return view('admin.requests.index', compact('requests'));
    }

    /** Sadece süper admin: Talep düzenleme formu */
    public function editRequest(RequestForm $requestForm)
    {
        $requestForm->load(['items.unit', 'user', 'department']);
        $units = Unit::orderBy('name')->get();
        return view('admin.requests.edit', compact('requestForm', 'units'));
    }

    /** Sadece süper admin: Talep güncelleme */
    public function updateRequest(Request $request, RequestForm $requestForm)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending_chief,pending_manager,pending_purchasing,approved,rejected',
            'rejection_reason' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|exists:request_items,id',
            'items.*.content' => 'nullable|string|max:1000',
            'items.*.link' => 'nullable|string|max:500',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity' => 'nullable|numeric|min:0',
        ]);

        $requestForm->update([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason,
        ]);

        $updatedIds = [];
        if (is_array($request->items)) {
            foreach ($request->items as $row) {
                $content = $row['content'] ?? '';
                $link = $row['link'] ?? null;
                if (trim($content) === '') {
                    continue;
                }
                $unitId = ! empty($row['unit_id']) ? $row['unit_id'] : null;
                $quantity = isset($row['quantity']) && $row['quantity'] !== '' ? $row['quantity'] : null;
                if (! empty($row['id'])) {
                    $item = RequestItem::where('request_form_id', $requestForm->id)->find($row['id']);
                    if ($item) {
                        $item->update(['content' => $content, 'link' => $link, 'unit_id' => $unitId, 'quantity' => $quantity]);
                        $updatedIds[] = $item->id;
                    }
                } else {
                    $newItem = RequestItem::create([
                        'request_form_id' => $requestForm->id,
                        'content' => $content,
                        'link' => $link,
                        'unit_id' => $unitId,
                        'quantity' => $quantity,
                    ]);
                    $updatedIds[] = $newItem->id;
                }
            }
        }

        RequestItem::where('request_form_id', $requestForm->id)->whereNotIn('id', $updatedIds)->delete();

        RequestHistory::create([
            'request_form_id' => $requestForm->id,
            'user_id' => auth()->id(),
            'action' => 'admin_updated',
            'note' => 'Talep süper admin tarafından güncellendi.',
        ]);

        return redirect()->route('admin.requests')->with('success', __('Request updated successfully.'));
    }
}
