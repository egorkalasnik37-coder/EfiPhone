<?php
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

$phones = [];
$file   = __DIR__ . '/data/phones.csv';

if (file_exists($file)) {
    $handle = fopen($file, 'r');
    // Strip UTF-8 BOM if present
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($handle);
    // Detect separator from first line
    $pos   = ftell($handle);
    $first = fgets($handle);
    fseek($handle, $pos);
    $sep = (substr_count($first, ';') > substr_count($first, ',')) ? ';' : ',';
    fgetcsv($handle, 0, $sep); // skip header row
    while (($row = fgetcsv($handle, 0, $sep)) !== false) {
        if (count($row) < 12) continue;
        if (!trim($row[0])) continue;
        if (trim($row[11]) !== '1') continue;
        $bat = explode('|', trim($row[4]));
        $phones[] = [
            'brand'     => trim($row[0]),
            'model'     => trim($row[1]),
            'memory'    => trim($row[2]),
            'color'     => trim($row[3]),
            'bat_mah'   => trim($bat[0]),
            'bat_pct'   => isset($bat[1]) ? trim($bat[1]) : '',
            'year'      => trim($row[5]),
            'grade'     => trim($row[6]),
            'price'     => (int)trim($row[7]),
            'price_old' => (int)trim($row[8]),
            'photo'     => trim($row[9]),
            'note'      => trim($row[10]),
        ];
    }
    fclose($handle);
}

// Group brand → list of indices into $phones
$brands = [];
foreach ($phones as $idx => $p) {
    $brands[$p['brand']][] = $idx;
}

// json_encode WITHOUT JSON_UNESCAPED_UNICODE:
// Cyrillic → \uXXXX (pure ASCII) — safe at any server encoding
$phones_json = json_encode(array_values($phones));
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Каталог — EfiPhone</title>
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="animation.css">
<style>
  *{box-sizing:border-box}
  body{font-family:'Unbounded',sans-serif;margin:0;background:#f5f5f5;display:flex;flex-direction:column;min-height:100vh}
  .site-header{background:#fff;border-bottom:2px solid #d2dbd2;padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between;position:fixed;top:0;left:0;right:0;z-index:100}
  .logo{font-size:22px;font-weight:700;letter-spacing:2px;color:#111}
  .logo span{color:#0a7a48}
  .site-header nav{display:flex;gap:4px}
  .site-header nav a{padding:8px 14px;border-radius:8px;font-size:12px;font-weight:500;color:#546254;text-decoration:none}
  .site-header nav a:hover{background:#f0f3f0;color:#111}
  .site-header nav a.active{background:rgba(10,122,72,.1);color:#0a7a48;font-weight:700}
  .cart-btn{display:flex;align-items:center;gap:8px;background:#0a7a48;color:#fff;font-size:12px;font-weight:700;padding:9px 18px;border-radius:50px;text-decoration:none}
  .cart-btn:hover{background:#076038}
  .badge{background:#fff;color:#0a7a48;font-size:11px;font-weight:800;min-width:20px;height:20px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center}
  main{flex:1;padding-top:80px}
  .content{background:#fffde7;padding:24px 32px}
  /* Grades */
  .grade-ap{background:#d4edda;color:#155724;padding:3px 10px;border-radius:20px;font-size:11px;display:inline-block;white-space:nowrap}
  .grade-a {background:#cce5ff;color:#004085;padding:3px 10px;border-radius:20px;font-size:11px;display:inline-block;white-space:nowrap}
  .grade-b {background:#fff3cd;color:#856404;padding:3px 10px;border-radius:20px;font-size:11px;display:inline-block;white-space:nowrap}
  /* Grid */
  .cards-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:8px}
  /* Card */
  .phone-card{background:#fff;border:1.5px solid #e0e0e0;border-radius:12px;overflow:hidden;cursor:pointer;transition:box-shadow .2s,border-color .2s;display:flex;flex-direction:column}
  .phone-card:hover{border-color:#0a7a48;box-shadow:0 4px 18px rgba(10,122,72,.13)}
  .phone-card.hidden{display:none !important}
  .card-img{background:#f8f8f8;display:flex;align-items:center;justify-content:center;height:240px;padding:8px;border-bottom:1px solid #f0f0f0}
  .card-img img{width:100%;height:100%;object-fit:contain}
  .card-body{padding:12px 14px;display:flex;flex-direction:column;gap:4px;flex:1}
  .card-name{font-size:12px;font-weight:700;color:#111;line-height:1.4;margin-top:4px}
  .card-sub{font-size:10px;color:#777}
  .card-prices{display:flex;align-items:baseline;gap:8px;margin-top:4px}
  .card-price{font-size:16px;font-weight:800;color:#0a7a48}
  .card-old{font-size:10px;color:#aaa;text-decoration:line-through}
  .card-btn{margin-top:auto;padding:9px;background:#0a7a48;color:#fff;border:none;border-radius:8px;font-size:10px;font-weight:700;cursor:pointer;font-family:'Unbounded',sans-serif;width:100%}
  .card-btn:hover{background:#076038}
  /* Filter bar */
  .section-title{color:#0a7a48;margin:20px 0 4px;font-size:15px}
  .brand-block.hidden{display:none !important}
  .filter-bar{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;align-items:center;padding:12px 16px;background:#fff;border-radius:12px;border:1.5px solid #d2dbd2}
  .filter-lbl{font-size:11px;font-weight:700;color:#546254;letter-spacing:1px;text-transform:uppercase}
  .fbtn{padding:5px 13px;border-radius:20px;border:1.5px solid #d2dbd2;background:#f5f5f5;color:#546254;font-size:11px;font-weight:600;cursor:pointer;font-family:'Unbounded',sans-serif}
  .fbtn:hover{border-color:#0a7a48;color:#0a7a48}
  .fbtn.active{background:#0a7a48;color:#fff;border-color:#0a7a48}
  .filter-sep{width:1px;height:20px;background:#d2dbd2;margin:0 4px}
  /* Modal */
  .modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:200}
  .modal-bg.open{display:block}
  .modal-box{display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;width:780px;max-width:95vw;max-height:90vh;overflow:hidden;z-index:201}
  .modal-box.open{display:flex}
  .modal-close{position:absolute;top:12px;right:14px;width:32px;height:32px;background:#f0f3f0;border:1px solid #d2dbd2;border-radius:8px;font-size:18px;color:#546254;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:10;line-height:1}
  .modal-close:hover{background:#d2dbd2}
  .modal-left{width:300px;min-width:300px;background:#f5f5f5;border-right:1px solid #d2dbd2;display:flex;flex-direction:column}
  .car-stage{flex:1;min-height:280px;display:flex;align-items:center;justify-content:center;padding:20px}
  .car-stage img{max-height:240px;max-width:240px;object-fit:contain;border-radius:12px}
  .car-nav{padding:12px 16px;border-top:1px solid #d2dbd2;background:#fff;display:flex;align-items:center;justify-content:space-between}
  .car-dots{display:flex;gap:5px}
  .car-dot{width:7px;height:7px;border-radius:4px;background:#d2dbd2;border:none;cursor:pointer;padding:0}
  .car-dot.active{width:18px;background:#0a7a48}
  .car-arrows{display:flex;gap:5px}
  .car-arrow{width:30px;height:30px;background:#f5f5f5;border:1px solid #d2dbd2;border-radius:7px;font-size:15px;color:#546254;display:flex;align-items:center;justify-content:center;cursor:pointer}
  .car-arrow:hover{background:#d2dbd2}
  .modal-right{flex:1;padding:24px;overflow-y:auto;display:flex;flex-direction:column;gap:10px}
  .modal-brand{font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#546254}
  .modal-name{font-size:20px;font-weight:700}
  .modal-price-big{font-size:22px;font-weight:800;color:#0a7a48}
  .modal-old-p{font-size:13px;color:#999;text-decoration:line-through}
  .spec-tbl{width:100%;border-collapse:collapse;font-size:12px;background:#fff;border-radius:10px;overflow:hidden;border:1.5px solid #d2dbd2}
  .spec-tbl th{padding:8px 12px;text-align:left;font-weight:700;border-bottom:1px solid #d2dbd2;color:#546254;width:40%;font-size:11px}
  .spec-tbl td{padding:8px 12px;border-bottom:1px solid #d2dbd2}
  .spec-tbl tr:last-child th,.spec-tbl tr:last-child td{border-bottom:none}
  .modal-note{background:#f5f5f5;border:1px solid #d2dbd2;border-radius:10px;padding:12px 14px;font-size:12px;color:#546254;line-height:1.7}
  .modal-note b{display:block;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:4px;color:#0a7a48}
  .modal-add{padding:13px;border-radius:50px;background:#0a7a48;color:#fff;font-weight:700;font-size:13px;border:none;cursor:pointer;font-family:'Unbounded',sans-serif;width:100%}
  .modal-add:hover{background:#076038}
  /* Footer */
  .footer{background:#222;color:#fff;text-align:center;padding:16px;font-size:13px;margin-top:auto}
  #toast{display:none;position:fixed;bottom:30px;left:50%;transform:translateX(-50%);background:#0a7a48;color:#fff;padding:14px 28px;border-radius:30px;font-size:13px;font-family:'Unbounded',sans-serif;z-index:999;white-space:nowrap}
  .no-data{background:#fff3cd;color:#856404;padding:20px;border-radius:10px;font-size:13px;text-align:center;margin-top:20px}
</style>
</head>
<body>

<header class="site-header">
  <div class="logo">EFI<span>PHONE</span></div>
  <nav>
    <a href="index.html">Головна</a>
    <a href="catalog.php" class="active">Каталог</a>
    <a href="about.html">Про нас</a>
    <a href="guarantee.html">Гарантія</a>
    <a href="contact.php">Контакти</a>
  </nav>
  <a href="cart.php" class="cart-btn">&#128722; Кошик <span class="badge" id="cart-count">0</span></a>
</header>

<main>
<div class="content">

  <h2 style="text-align:center;color:#0a7a48;margin-bottom:8px;">Каталог смартфонів</h2>
  <p style="font-size:13px;color:#555;margin-bottom:16px;">
    <span class="grade-ap">A+</span> — як новий &nbsp;
    <span class="grade-a">A</span> — відмінний стан &nbsp;
    <span class="grade-b">B</span> — хороший стан
  </p>

<?php if (empty($phones)): ?>
  <div class="no-data">&#9888; Файл <strong>data/phones.csv</strong> не знайдено або порожній.</div>
<?php else: ?>

  <div class="filter-bar">
    <span class="filter-lbl">Бренд:</span>
    <button class="fbtn active" onclick="setFilter('brand','all',this)">Всі</button>
    <?php foreach (array_keys($brands) as $b): ?>
    <button class="fbtn" onclick="setFilter('brand',<?= json_encode($b) ?>,this)"><?= htmlspecialchars($b) ?></button>
    <?php endforeach; ?>
    <div class="filter-sep"></div>
    <span class="filter-lbl">Стан:</span>
    <button class="fbtn active" onclick="setFilter('grade','all',this)">Всі</button>
    <button class="fbtn" onclick="setFilter('grade','A+',this)">A+</button>
    <button class="fbtn" onclick="setFilter('grade','A',this)">A</button>
    <button class="fbtn" onclick="setFilter('grade','B',this)">B</button>
  </div>

  <?php
  $icons = ['Apple'=>'&#127822;','Samsung'=>'&#128247;','Google'=>'&#128241;','Xiaomi'=>'&#128242;','OnePlus'=>'&#10133;','OPPO'=>'&#128243;'];
  foreach ($brands as $brand_name => $idx_list):
    $bsafe = htmlspecialchars($brand_name);
  ?>
  <div class="brand-block" data-brand="<?= $bsafe ?>">
    <h3 class="section-title"><?= $icons[$brand_name] ?? '&#128241;' ?> <?= $bsafe ?></h3>
    <div class="cards-grid">
    <?php foreach ($idx_list as $idx):
      $p  = $phones[$idx];
      $gc = $p['grade'] === 'A+' ? 'grade-ap' : ($p['grade'] === 'B' ? 'grade-b' : 'grade-a');
    ?>
      <div class="phone-card"
           data-idx="<?= (int)$idx ?>"
           data-brand="<?= $bsafe ?>"
           data-grade="<?= htmlspecialchars($p['grade']) ?>">
        <div class="card-img">
          <img src="<?= htmlspecialchars($p['photo']) ?>"
               alt="<?= htmlspecialchars($p['model']) ?>"
               onerror="this.style.opacity='0.1'">
        </div>
        <div class="card-body">
          <span class="<?= $gc ?>"><?= htmlspecialchars($p['grade']) ?></span>
          <div class="card-name"><?= htmlspecialchars($p['brand']) ?> <?= htmlspecialchars($p['model']) ?></div>
          <div class="card-sub"><?= htmlspecialchars($p['memory']) ?> &middot; <?= htmlspecialchars($p['color']) ?></div>
          <div class="card-sub">АКБ: <?= htmlspecialchars($p['bat_mah']) ?> мАг &middot; <?= htmlspecialchars($p['bat_pct']) ?>% &middot; <?= htmlspecialchars($p['year']) ?></div>
          <div class="card-prices">
            <span class="card-price"><?= number_format($p['price'],0,',',' ') ?> &#8372;</span>
            <span class="card-old"><?= number_format($p['price_old'],0,',',' ') ?> &#8372;</span>
          </div>
          <button class="card-btn">До кошика</button>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>

<?php endif; ?>
</div>
</main>

<div class="footer">&#169; 2026 EfiPhone.com &middot; Всі права захищено</div>
<div id="toast">Додано до кошика &#10003;</div>

<div class="modal-bg" id="modal-bg"></div>
<div class="modal-box" id="modal-box">
  <button class="modal-close" id="modal-close">&#215;</button>
  <div class="modal-left">
    <div class="car-stage">
      <img id="car-img" src="" alt="">
    </div>
    <div class="car-nav">
      <div class="car-dots">
        <button class="car-dot active"></button>
      </div>
      <div class="car-arrows">
        <button class="car-arrow">&#8592;</button>
        <button class="car-arrow">&#8594;</button>
      </div>
    </div>
  </div>
  <div class="modal-right" id="modal-right"></div>
</div>

<script>
// Всі рядки — \uXXXX всередині, безпечно при будь-якому HTTP кодуванні
var PHONES_DATA = <?= $phones_json ?>;

// ── Лічильник кошика ──
function updateBadge() {
  try {
    var cart = JSON.parse(localStorage.getItem('cart') || '[]');
    var n = 0;
    for (var i = 0; i < cart.length; i++) n += (cart[i].qty || 1);
    document.getElementById('cart-count').textContent = n;
  } catch(e) {}
}
updateBadge();

// ── Фільтр ──
var activeBrand = 'all';
var activeGrade = 'all';

function setFilter(type, val, btn) {
  var group = btn.parentElement.querySelectorAll('.fbtn');
  for (var i = 0; i < group.length; i++) group[i].classList.remove('active');
  btn.classList.add('active');
  if (type === 'brand') activeBrand = val;
  else activeGrade = val;
  applyFilter();
}

function applyFilter() {
  var cards = document.querySelectorAll('.phone-card');
  for (var i = 0; i < cards.length; i++) {
    var c = cards[i];
    var show = (activeBrand === 'all' || c.getAttribute('data-brand') === activeBrand) &&
               (activeGrade === 'all' || c.getAttribute('data-grade') === activeGrade);
    if (show) c.classList.remove('hidden');
    else      c.classList.add('hidden');
  }
  var blocks = document.querySelectorAll('.brand-block');
  for (var i = 0; i < blocks.length; i++) {
    var vis = blocks[i].querySelectorAll('.phone-card:not(.hidden)');
    if (vis.length > 0) blocks[i].classList.remove('hidden');
    else                blocks[i].classList.add('hidden');
  }
}

// ── Модалка ──
function openModal(idx) {
  var p = PHONES_DATA[idx];
  if (!p) return;

  document.getElementById('car-img').src = p.photo;
  document.getElementById('car-img').alt = p.model;

  var gc = p.grade === 'A+' ? 'grade-ap' : (p.grade === 'B' ? 'grade-b' : 'grade-a');

  var noteBlock = '';
  if (p.note) {
    noteBlock = '<div class="modal-note"><b>&#1053;&#1102;&#1072;&#1085;&#1089;&#1080;</b>' + esc(p.note) + '</div>';
  }

  var right = document.getElementById('modal-right');
  right.innerHTML =
    '<div class="modal-brand">'   + esc(p.brand) + '</div>' +
    '<div class="modal-name">'    + esc(p.model) + '</div>' +
    '<div style="display:flex;align-items:baseline;gap:10px;margin:4px 0">' +
      '<span class="modal-price-big">' + fmt(p.price)     + ' &#8372;</span>' +
      '<span class="modal-old-p">'     + fmt(p.price_old) + ' &#8372;</span>' +
    '</div>' +
    '<table class="spec-tbl">' +
      '<tr><th>&#1055;&#1072;&#1084;&#8217;&#1103;&#1090;&#1100;</th><td>' + esc(p.memory)  + '</td></tr>' +
      '<tr><th>&#1050;&#1086;&#1083;&#1110;&#1088;</th><td>'                + esc(p.color)   + '</td></tr>' +
      '<tr><th>&#1040;&#1050;&#1041;</th><td>'                              + esc(p.bat_mah) + ' &#1084;&#1040;&#1075; &middot; ' + esc(p.bat_pct) + '%</td></tr>' +
      '<tr><th>&#1056;&#1110;&#1082;</th><td>'                              + esc(p.year)    + '</td></tr>' +
      '<tr><th>&#1057;&#1090;&#1072;&#1085;</th><td><span class="' + gc + '">' + esc(p.grade) + '</span></td></tr>' +
    '</table>' +
    noteBlock +
    '<button class="modal-add" id="m-add-btn">&#1044;&#1086;&#1076;&#1072;&#1090;&#1080; &#1076;&#1086; &#1082;&#1086;&#1096;&#1080;&#1082;&#1072;</button>';

  document.getElementById('m-add-btn').onclick = function() {
    addToCart(idx);
    closeModal();
  };

  document.getElementById('modal-bg').classList.add('open');
  document.getElementById('modal-box').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  document.getElementById('modal-bg').classList.remove('open');
  document.getElementById('modal-box').classList.remove('open');
  document.body.style.overflow = '';
}

function esc(s) {
  return String(s)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function fmt(n) {
  return Number(n).toLocaleString('uk-UA');
}

// ── Event delegation — один слухач для всіх кліків ──
document.addEventListener('click', function(e) {
  // 1. Кнопка "До кошика" на картці
  if (e.target.classList.contains('card-btn')) {
    e.stopPropagation();
    var card = e.target.closest('.phone-card');
    if (card) addToCart(parseInt(card.getAttribute('data-idx'), 10));
    return;
  }
  // 2. Клік по картці (але не по кнопці)
  var card = e.target.closest('.phone-card');
  if (card) {
    openModal(parseInt(card.getAttribute('data-idx'), 10));
    return;
  }
  // 3. Закрити модалку
  if (e.target.id === 'modal-bg') { closeModal(); return; }
  if (e.target.id === 'modal-close') { closeModal(); return; }
});

// ── Кошик ──
function addToCart(idx) {
  var p = PHONES_DATA[idx];
  if (!p) return;
  var cart = [];
  try { cart = JSON.parse(localStorage.getItem('cart') || '[]'); } catch(e) {}
  var found = false;
  for (var i = 0; i < cart.length; i++) {
    if (cart[i].idx === idx) { cart[i].qty = (cart[i].qty || 1) + 1; found = true; break; }
  }
  if (!found) cart.push({ idx: idx, name: p.model, price: p.price, qty: 1 });
  localStorage.setItem('cart', JSON.stringify(cart));
  updateBadge();
  showToast();
}

function showToast() {
  var t = document.getElementById('toast');
  t.style.display = 'block';
  clearTimeout(t._timer);
  t._timer = setTimeout(function() { t.style.display = 'none'; }, 2000);
}

// ── Анімація переходу ──
document.querySelectorAll('a').forEach(function(a) {
  a.addEventListener('click', function(e) {
    var h = this.getAttribute('href');
    if (h && h !== '#' && !h.startsWith('javascript') && !h.startsWith('mailto') && !h.startsWith('tel')) {
      e.preventDefault();
      document.body.classList.add('fade-out');
      var url = h;
      setTimeout(function() { window.location.href = url; }, 220);
    }
  });
});
</script>
</body>
</html>
