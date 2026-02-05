# İster — Talep ve Onay Yönetim Sistemi

Laravel tabanlı, çok kademeli onay akışına sahip bir **talep (request) yönetim sistemi**. Mühendisler talep oluşturur; talepler amir → müdür → satın alma sırasıyla onaylanır. Sadece ilgili kademe kendi kuyruğundaki talepleri görüntüleyebilir; tüm talepleri yalnızca admin görüntüleyebilir.

---

## Teknoloji

- **PHP** 8.2+
- **Laravel** 12
- **Laravel Breeze** (kimlik doğrulama)
- **Tailwind CSS**, **Alpine.js**
- **MySQL** / **SQLite** (veya Laravel’in desteklediği diğer veritabanları)

---

## Kurulum

### Gereksinimler

- PHP 8.2+
- Composer
- Node.js (opsiyonel; front-end derleme için)
- MySQL veya SQLite

### Adımlar

```bash
# Bağımlılıkları yükle
composer install

# Ortam dosyası
cp .env.example .env
php artisan key:generate

# .env içinde veritabanı ayarlarını yapın (DB_CONNECTION, DB_DATABASE, vb.)

# Veritabanı ve tablolar
php artisan migrate

# Varsayılan veriler (roller, birimler, örnek kullanıcılar, talepler)
php artisan db:seed
```

### Storage bağlantısı (görsel yükleme için)

```bash
php artisan storage:link
```

Görsel yükleme kullanılacaksa `public/storage` → `storage/app/public` sembolik linki oluşturulmalıdır.

---

## Sistem Özeti

| Özellik | Açıklama |
|--------|----------|
| **Roller** | Mühendis, Amir (Şef), Müdür, Satın Alma, Admin. Roller admin panelinden yönetilir. |
| **Bölümler** | Kullanıcı ve talep ataması için. Admin panelinden CRUD. |
| **Talepler** | Başlık, açıklama ve birden fazla **kalem** (içerik, link, birim, miktar, görsel). |
| **Onay akışı** | Mühendis oluşturur → Amir onaylar → Müdür onaylar → Satın Alma onaylar → Onaylandı. |
| **Görünürlük** | Her kademe yalnızca kendi onay kuyruğundaki talepleri görür; admin tüm talepleri görür. |

---

## Roller ve Yetkiler

| Rol | Erişim |
|-----|--------|
| **Mühendis** | Kendi taleplerini listeler, yeni talep oluşturur. Sadece kendi taleplerinin detayını görür. |
| **Amir (Şef)** | Sadece **kendi departmanındaki** ve **Şef Onayı Bekliyor** durumundaki talepleri görür ve onaylar/reddeder. |
| **Müdür** | Sadece **Müdür Onayı Bekliyor** durumundaki talepleri görür ve onaylar/reddeder. Amir onaylamadan müdür talep göremez. |
| **Satın Alma** | Sadece **Satın Alma Onayı Bekliyor** durumundaki talepleri görür ve onaylar/reddeder. |
| **Admin** | Tüm talepleri (tüm durumlarda) görüntüleyebilir ve düzenleyebilir. Bölüm, rol, birim ve kullanıcı yönetimi yapar. |

**Özet kural:** Bir alt kademe onaylamadan üst kademe talebi görüntüleyemez. Sadece admin tüm durumlardaki talepleri görüntüleyebilir.

---

## Talep Akışı (Durumlar)

1. **pending_chief** — Şef onayı bekliyor (Amir görür).
2. **pending_manager** — Müdür onayı bekliyor (Müdür görür).
3. **pending_purchasing** — Satın alma onayı bekliyor (Satın Alma görür).
4. **approved** — Tamamlandı.
5. **rejected** — Reddedildi (red sebebi kaydedilir).

Mühendis talep oluşturduğunda talep `pending_chief` ile başlar.

---

## Talep Oluşturma (Mühendis)

- **Başlık** ve **açıklama** zorunlu.
- **Kalemler:** Her kalemde:
  - **İçerik** (zorunlu)
  - **Birim** ve **miktar** (opsiyonel)
  - **Link** (opsiyonel)
  - **Görsel:**  
    - **Sistemden seç:** Modalda görsel adına göre arama, sayfalı liste, seçim. Seçim yapılınca yükleme alanı kilitlenir; “Seçimi iptal et” ile kaldırılabilir.  
    - **Görsel yükle:** Görsel adı (zorunlu) + dosya yükleme. Sistemden seçim yapılmışsa yükleme alanları devre dışıdır.

Görsel için: ya sistemden seçilir ya da yeni dosya yüklenir; ikisi birlikte kullanılamaz.

---

## Admin Paneli

Sadece **admin** rolündeki kullanıcılar `/admin/dashboard` ve altındaki sayfalara erişir.

| Modül | Açıklama |
|-------|----------|
| **Yönetim Paneli** | Ana sayfa; diğer modüllere linkler. |
| **Kullanıcı Yönetimi** | Kullanıcı listesi, oluşturma, düzenleme, silme. Rol ve bölüm ataması. |
| **Talep Listesi** | Tüm talepler; durum/tarih filtresi; detay ve talep düzenleme. |
| **Roller** | Rol listesi, ekleme, düzenleme (ad, slug, sıra), silme. Kullanıcıya atanmış rol silinemez. |
| **Bölümler** | Bölüm listesi, ekleme, düzenleme, silme. Kullanıcıya atanmış bölüm silinemez. |
| **Birimler** | Talep kalemleri için birimler (Adet, Kg, Litre vb.); CRUD. |

---

## Önemli Rotalar

| Rota | Açıklama |
|------|----------|
| `/` | Giriş sayfasına yönlendirir. |
| `/dashboard` | Role göre: Mühendis → Taleplerim, Amir/Müdür/Satın Alma → Bekleyen Onaylar, Admin → Admin paneli. |
| `/requests` | Mühendis: kendi talepleri. Diğer roller: Bekleyen Onaylar sayfasına yönlendirilir. |
| `/requests/create` | Yeni talep (sadece mühendis). |
| `/approvals` | Bekleyen onaylar listesi (Amir, Müdür, Satın Alma). |
| `/admin/dashboard` | Admin ana sayfa. |
| `/admin/requests` | Tüm talepler (admin). |
| `/admin/users` | Kullanıcı yönetimi. |
| `/admin/roles` | Rol yönetimi. |
| `/admin/departments` | Bölüm yönetimi. |
| `/admin/units` | Birim yönetimi. |

---

## Varsayılan Giriş Bilgileri (Seed Sonrası)

Tüm kullanıcılar için varsayılan şifre: **`password`**

| Rol | E-posta |
|-----|---------|
| Admin | admin@example.com |
| Mühendis | engineer@example.com |
| Şef | chief@example.com |
| Müdür | manager@example.com |
| Satın Alma | purchasing@example.com |

Detay için proje kökündeki `girisbilgileri.md` dosyasına bakılabilir.

---

## Veritabanı Yapısı (Özet)

- **users** — name, email, password, role (slug), department_id (soft delete destekli).
- **departments** — name.
- **roles** — name, slug, sort_order.
- **request_forms** — user_id, department_id, request_no, title, description, status, rejection_reason.
- **request_items** — request_form_id, content, link, unit_id, quantity, image_path, image_name.
- **request_histories** — request_form_id, user_id, action, note (onay/red/oluşturma kayıtları).
- **units** — name, symbol.

---

## Çoklu Dil

Arayüz ve mesajlar **Türkçe** (varsayılan) ve İngilizce için hazırlanabilir. Çeviriler `lang/tr.json` ve `lang/tr/` altında tutulur.

---

## Lisans

Bu proje [MIT lisansı](https://opensource.org/licenses/MIT) altında lisanslanmıştır.
