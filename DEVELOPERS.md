# Developers Guide – SVG Studio v3.1

Kodu kurcalamak, yeni özellik eklemek veya PR göndermek isteyenler için teknik rehber.

## 1 | Dizin Yapısı

```
/
├─ index.php            # Tek sayfa uygulama
├─ svgs/                # SVG koleksiyon klasörü (alt klasör serbest)
├─ docs/                # README görselleri
└─ …                    # CDN’ler sayesinde başka asset yok
```

## 2 | Temel Bileşenler

| Bölüm | İşlev | Not |
|-------|-------|-----|
| **PHP – `list_svg()`** | `/svgs` ağacını gezer, yol + regex etiketleri JSON’a basar | **Salt okuma** – yükleme yok |
| **JS State** | `data`, `filt`, `sel` (`Set`) | UI değişiminde `render()` çağrılır |
| **Favoriler** | `localStorage` anahtarı **`favs`** | Basit string dizisi |
| **Düzenleyici** | DOMParser ⇄ XMLSerializer | Renk & boyut → `applyEdits()` |
| **PNG çıktısı** | `canvg` → `canvas.toBlob()` | Sunucuya gereksinim yok |

## 3 | Bağımlılıklar

| Kütüphane (CDN) | Kullanımı |
|-----------------|-----------|
| **Bootstrap 5.3** | Modal, grid, bileşenler |
| **Font Awesome 6** | İkonlar |
| **JSZip + FileSaver** | Seçili SVG’leri ZIP indirme |
| **canvg 3** | SVG → PNG rasterizasyonu |

> Bunları NPM ile yerel olarak paketleyip bundler kullanmak da mümkün.

## 4 | Geliştirme Senaryoları

### 4.1 Gradient desteği
`applyEdits()` içinde `linearGradient` / `radialGradient` öğelerine de renk atayın, renk seçici çift picker olabilir.

### 4.2 Tema tabanlı zemin
`checker` yerine `prefers-color-scheme` ile ışık / karanlık sınıfları uygulayın.

### 4.3 Sunucuya SVG yükleme (opsiyonel)

```php
// index.php başına:
if(isset($_FILES['svg'])){
   move_uploaded_file($_FILES['svg']['tmp_name'],
       $svg_dir.'/user/'.basename($_FILES['svg']['name']));
   header('Location: .?upload=ok'); exit;
}
```

> **Güvenlik:** MIME + SVG içerik sanitizasyonu şart!

## 5 | Kod Stili

* JS → ESLint / Prettier (standard)
* PHP → Intelephense

## 6 | Katkı Akışı

1. Fork → `feat/awesome` branch  
2. Açıklamalı commit mesajları  
3. Pull Request + ekran görüntüsü / GIF  
4. _Squash & merge_

Mutlu kodlamalar! 🤘
