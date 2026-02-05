<?php

namespace App\Http\Controllers;

use App\Models\RequestForm;
use App\Models\RequestItem;
use App\Models\RequestHistory;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RequestController extends Controller
{
    // Engineer: List My Requests. Amir/Müdür/Satın Alma sadece onay sayfasından erişir.
    public function index()
    {
        $user = Auth::user();
        if ($user->role !== 'engineer') {
            return redirect()->route('requests.approvals');
        }
        $requests = RequestForm::where('user_id', $user->id)->with('items')->latest()->get();
        return view('requests.index', compact('requests'));
    }

    // Engineer: Create Form — sadece mühendis talep oluşturabilir
    public function create()
    {
        if (Auth::user()->role !== 'engineer') {
            abort(403, __('Only engineers can create requests.'));
        }
        $units = Unit::orderBy('name')->get();
        return view('requests.create', compact('units'));
    }

    /** Sistemdeki görsellere isimle arama (talep oluştururken "Sistemden seç" için); sayfalama destekli */
    public function searchImages(Request $request)
    {
        $q = $request->input('q', '');
        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(6, min(24, $perPage));
        $page = max(1, (int) $request->input('page', 1));

        $query = RequestItem::whereNotNull('image_path')
            ->where(function ($qry) {
                $qry->whereNotNull('image_name')->where('image_name', '!=', '');
            });
        if (strlen($q) > 0) {
            $query->where('image_name', 'like', '%' . $q . '%');
        }
        $query->select('image_name', 'image_path')->distinct()->orderBy('image_name');
        $total = $query->count();
        $items = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        $images = $items->map(fn ($i) => [
            'name' => $i->image_name,
            'path' => $i->image_path,
            'url' => asset('storage/' . $i->image_path),
        ]);
        return response()->json([
            'images' => $images,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
            'per_page' => $perPage,
            'total' => $total,
        ]);
    }

    // Engineer: Store Request
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'engineer') {
            abort(403, __('Only engineers can create requests.'));
        }
        $rules = [
            'title' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.content' => 'required|string',
            'items.*.link' => 'nullable|string|max:500',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'items.*.image_name' => 'nullable|string|max:255',
            'items.*.image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:5120',
            'items.*.image_from_system_path' => 'nullable|string|max:500',
            'items.*.image_from_system_name' => 'nullable|string|max:255',
        ];
        $request->validate($rules);

        $validSystemPaths = RequestItem::whereNotNull('image_path')->pluck('image_path')->unique()->values()->toArray();

        foreach ($request->items as $idx => $item) {
            if ($request->hasFile("items.{$idx}.image") && $request->file("items.{$idx}.image")->isValid()) {
                if (empty(trim($request->input("items.{$idx}.image_name") ?? ''))) {
                    return back()->withErrors(["items.{$idx}.image_name" => __('Image name is required when uploading an image.')])->withInput();
                }
            }
        }

        DB::transaction(function () use ($request, $validSystemPaths) {
            $form = RequestForm::create([
                'user_id' => Auth::id(),
                'department_id' => Auth::user()->department_id,
                'request_no' => 'REQ-' . date('YmdHis') . '-' . rand(100, 999),
                'title' => $request->title,
                'description' => $request->description,
                'status' => 'pending_chief',
            ]);

            foreach ($request->items as $idx => $item) {
                $imagePath = null;
                $imageName = null;
                $fromSystem = trim($request->input("items.{$idx}.image_from_system_path") ?? '');
                if ($fromSystem !== '' && in_array($fromSystem, $validSystemPaths)) {
                    $imagePath = $fromSystem;
                    $imageName = trim($request->input("items.{$idx}.image_from_system_name") ?? '');
                } elseif ($request->hasFile("items.{$idx}.image") && $request->file("items.{$idx}.image")->isValid()) {
                    $imagePath = $request->file("items.{$idx}.image")->store('request-images', 'public');
                    $imageName = trim($request->input("items.{$idx}.image_name") ?? '');
                }
                RequestItem::create([
                    'request_form_id' => $form->id,
                    'content' => $item['content'],
                    'link' => $item['link'] ?? null,
                    'unit_id' => ! empty($item['unit_id']) ? $item['unit_id'] : null,
                    'quantity' => isset($item['quantity']) && $item['quantity'] !== '' ? $item['quantity'] : null,
                    'image_path' => $imagePath,
                    'image_name' => $imageName ?: null,
                ]);
            }

            RequestHistory::create([
                'request_form_id' => $form->id,
                'user_id' => Auth::id(),
                'action' => 'created',
                'note' => 'Request created',
            ]);
        });

        return redirect()->route('requests.index')->with('success', 'Request created successfully.');
    }

    public function show(RequestForm $requestForm)
    {
        $user = Auth::user();

        // Sadece admin tüm durumlardaki talepleri görüntüleyebilir. Diğerleri: bir alt birim onaylamadan üst görüntüleyemez.
        if ($user->role === 'admin') {
            // Admin: tüm talepleri görebilir (detay için admin/requests üzerinden de gidebilir)
        } elseif ($user->role === 'engineer') {
            if ($requestForm->user_id !== $user->id) {
                abort(403, __('You can only view your own requests.'));
            }
        } elseif ($user->role === 'chief') {
            if ($requestForm->status !== 'pending_chief' || $requestForm->department_id != $user->department_id) {
                abort(403, __('You can only view requests pending your approval in your department.'));
            }
        } elseif ($user->role === 'manager') {
            if ($requestForm->status !== 'pending_manager') {
                abort(403, __('You can only view requests pending your approval.'));
            }
        } elseif ($user->role === 'purchasing') {
            if ($requestForm->status !== 'pending_purchasing') {
                abort(403, __('You can only view requests pending your approval.'));
            }
        } else {
            abort(403);
        }

        $requestForm->load(['items.unit', 'histories.user', 'user', 'department']);
        return view('requests.show', compact('requestForm'));
    }

    // Amir / Müdür / Satın Alma: Sadece kendi onay kuyruğundaki talepler. Mühendis ve admin bu sayfaya gelmemeli.
    public function approvals()
    {
        $user = Auth::user();
        if ($user->role === 'engineer') {
            return redirect()->route('requests.index');
        }
        if ($user->role === 'admin') {
            return redirect()->route('admin.requests');
        }

        $query = RequestForm::with(['user', 'items']);
        if ($user->role === 'chief') {
            $query->where('department_id', $user->department_id)->where('status', 'pending_chief');
        } elseif ($user->role === 'manager') {
            $query->where('status', 'pending_manager');
        } elseif ($user->role === 'purchasing') {
            $query->where('status', 'pending_purchasing');
        } else {
            $query->whereRaw('1 = 0'); // Diğer roller boş liste
        }

        $requests = $query->latest()->get();
        return view('requests.approvals', compact('requests'));
    }

    // Action: Approve — sadece ilgili birim onaylayabilir; amir sadece kendi departmanındaki pending_chief
    public function approve(Request $request, RequestForm $requestForm)
    {
        $user = Auth::user();
        $nextStatus = '';

        if ($user->role === 'chief' && $requestForm->status === 'pending_chief') {
            if ($requestForm->department_id != $user->department_id) {
                abort(403, __('You can only approve requests from your department.'));
            }
            $nextStatus = 'pending_manager';
        } elseif ($user->role === 'manager' && $requestForm->status === 'pending_manager') {
            $nextStatus = 'pending_purchasing';
        } elseif ($user->role === 'purchasing' && $requestForm->status === 'pending_purchasing') {
            $nextStatus = 'approved';
        } else {
            abort(403, __('You cannot approve this request.'));
        }

        $requestForm->update(['status' => $nextStatus]);

        RequestHistory::create([
            'request_form_id' => $requestForm->id,
            'user_id' => $user->id,
            'action' => 'approved',
            'note' => 'Approved by ' . $user->role,
        ]);

        return back()->with('success', 'Request approved.');
    }

    // Action: Reject — onay yetkisi olan aynı rol reddedebilir
    public function reject(Request $request, RequestForm $requestForm)
    {
        $user = Auth::user();
        $canReject = false;
        if ($user->role === 'chief' && $requestForm->status === 'pending_chief' && $requestForm->department_id == $user->department_id) {
            $canReject = true;
        } elseif ($user->role === 'manager' && $requestForm->status === 'pending_manager') {
            $canReject = true;
        } elseif ($user->role === 'purchasing' && $requestForm->status === 'pending_purchasing') {
            $canReject = true;
        }
        if (!$canReject) {
            abort(403, __('You cannot reject this request.'));
        }

        $request->validate(['reason' => 'required|string']);

        $requestForm->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason
        ]);

        RequestHistory::create([
            'request_form_id' => $requestForm->id,
            'user_id' => Auth::id(),
            'action' => 'rejected',
            'note' => $request->reason,
        ]);

        return back()->with('success', 'Request rejected.');
    }
}
