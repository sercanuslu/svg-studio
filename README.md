# SVG Studio v3.1

> 🌈 SVG koleksiyonunuzu arayın, favorileyin, düzenleyin ve tek tıkla indirin.


## Özellikler

| Kategori | Başlık | Açıklama |
|----------|--------|----------|
| **Gezinme** | Arama & klasör filtresi | Dosya adına + otomatik etiketlere göre (örn. `circle-check`) anlık filtreleme |
| | Sayfalama | 32’li sayfalar, dinamik kısaltılmış pagination |
| **Kişiselleştirme** | ⭐ Favoriler | `localStorage` tabanlı, oturumlar arası kalıcı |
| | ✅ Toplu seçim | Checkbox ile birden fazla ikon seç → “ZIP indir” |
| **Düzenleyici** | 🎨 Canlı renk değişimi | Fill / Stroke renk seçici, kareli veya düz ön-izleme zemini |
| | 📐 Ölçek | Genişlik & yükseklik piksel ayarı |
| | ↷ Kopyala | Tek tıkla SVG kodunu panoya kopyala |
| | ⬇️ SVG / PNG indirme | Güncel hâli SVG olarak veya `canvg` ile raster PNG |
| **Diğer** | 🖇 Drag-&-drop | Kendi SVG’ni sürükle, anında düzenleyicide aç |
| | 🔒 Giriş ekranı | Basit kullanıcı = **user**, şifre = **password** |

## Hızlı Başlangıç

```bash
git clone https://github.com/yourname/svg-studio.git
cd svg-studio
php -S localhost:8000        # veya Nginx/Apache sanal host
```

1. `/svgs` klasörüne istediğiniz kadar alt klasör ile SVG koyun.  
2. Tarayıcıda `http://localhost:8000` → **user / password** ile giriş yapın.  
3. Keyfinize bakın. 🤘

## Gereksinimler

| Katman | Sürüm / Not |
|--------|-------------|
| PHP    | 8.x (7.4 de çalışır) |
| Tarayıcı | Modern ES6 (Chrome, Firefox, Safari, Edge…) |
| Sunucu bağımlılığı | **Yok** – tüm düzenleme ve dışa aktarma istemci tarafında |

## Lisans

MIT © 2025 Sercan USLU — kopyalayın, çatallayın, yıldız bırakın!
