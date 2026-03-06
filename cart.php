<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Кошик — EfiPhone</title>
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="animation.css">
<style>
  body{font-family:'Unbounded',sans-serif;margin:0;background-color:#f5f5f5;display:flex;flex-direction:column;min-height:100vh}
  .site-header{background-color:#fff;border-bottom:2px solid #d2dbd2;padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between;position:fixed;top:0;left:0;right:0;z-index:100}
  .logo{font-size:22px;font-weight:700;letter-spacing:2px;color:#111}
  .logo span{color:#0a7a48}
  .site-header nav{display:flex;gap:4px}
  .site-header nav a{padding:8px 14px;border-radius:8px;font-size:12px;font-weight:500;color:#546254;text-decoration:none}
  .site-header nav a:hover{background-color:#f0f3f0;color:#111}
  .cart-btn{display:flex;align-items:center;gap:8px;background-color:#0a7a48;color:#fff;font-size:12px;font-weight:700;padding:9px 18px;border-radius:50px;text-decoration:none}
  .cart-btn:hover{background-color:#076038}
  .badge{background:#fff;color:#0a7a48;font-size:11px;font-weight:800;min-width:20px;height:20px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center}
  main{flex:1;padding-top:80px}
  .content{background-color:#fffde7;padding:30px 40px}
  .cart-table{border-collapse:collapse;width:100%;margin-top:20px;border-radius:10px;overflow:hidden}
  .cart-table th{background-color:#0a7a48;color:white;padding:12px;text-align:left;font-size:13px}
  .cart-table td{padding:12px;border-bottom:1px solid #ddd;font-size:13px}
  .btn-del{padding:6px 14px;background-color:#e53935;color:white;border:none;border-radius:5px;cursor:pointer;font-family:'Unbounded',sans-serif;font-size:12px}
  .btn-del:hover{background-color:#b71c1c}
  .order-form{margin-top:28px;background-color:#fff;border:1.5px solid #d2dbd2;border-radius:12px;padding:24px;max-width:480px}
  .order-form h3{color:#0a7a48;margin:0 0 16px;font-size:15px}
  .field{width:100%;padding:10px;margin-top:5px;border:2px solid #d2dbd2;border-radius:8px;font-family:'Unbounded',sans-serif;font-size:13px;box-sizing:border-box;outline:none}
  .field:focus{border-color:#0a7a48}
  .flabel{font-size:12px;color:#546254;margin-top:14px;display:block}
  .btn-order{margin-top:16px;padding:14px;background-color:#0a7a48;color:white;border:none;border-radius:50px;font-size:14px;cursor:pointer;font-family:'Unbounded',sans-serif;width:100%}
  .btn-order:hover{background-color:#076038}
  .btn-order:disabled{background-color:#999;cursor:wait}
  .result-ok{background-color:#d4edda;color:#155724;padding:14px 18px;border-radius:10px;font-size:13px;margin-top:16px;display:none}
  .result-err{background-color:#f8d7da;color:#721c24;padding:14px 18px;border-radius:10px;font-size:13px;margin-top:16px;display:none}
  .footer{background-color:#222;color:white;text-align:center;padding:16px;font-size:13px;margin-top:auto}
</style>
</head>
<body>

<header class="site-header">
  <div class="logo">EFI<span>PHONE</span></div>
  <nav>
    <a href="index.html">Головна</a>
    <a href="catalog.php">Каталог</a>
    <a href="about.html">Про нас</a>
    <a href="guarantee.html">Гарантія</a>
    <a href="contact.php">Контакти</a>
  </nav>
  <a href="cart.php" class="cart-btn">&#128722; Кошик <span class="badge" id="cart-count">0</span></a>
</header>

<main>
<div class="content">
  <h2 style="color:#0a7a48;margin-bottom:16px;">Ваш кошик</h2>
  <div id="cart-content"></div>
</div>
</main>

<div class="footer">&#169; 2026 EfiPhone.com . Усі права захищено</div>

<script>
var cart=JSON.parse(localStorage.getItem('cart')||'[]');
var block=document.getElementById('cart-content');
var ct=0; for(var i=0;i<cart.length;i++) ct+=cart[i].qty;
document.getElementById('cart-count').textContent=ct;

if(cart.length===0){
  block.innerHTML='<p style="color:#777;margin-top:8px;">Кошик порожній. <p> <a href="catalog.php" style="color:#0a7a48;">Перейти до каталогу</a></p>';
} else {
  var html='<table class="cart-table"><tr><th>Товар</th><th>Ціна</th><th>К-сть</th><th>Сума</th><th></th></tr>';
  var total=0;
  var itemsList='';
  for(var i=0;i<cart.length;i++){
    var it=cart[i],sum=it.price*it.qty; total+=sum;
    itemsList+=it.name+' x'+it.qty+'; ';
    html+='<tr><td>'+it.name+'</td><td>'+it.price.toLocaleString('uk-UA')+' &#8372;</td><td>'+it.qty+'</td><td>'+sum.toLocaleString('uk-UA')+' &#8372;</td>';
    html+='<td><button class="btn-del" onclick="removeItem('+i+')">&#10005;</button></td></tr>';
  }
  html+='</table>';
  html+='<p style="text-align:right;font-size:18px;font-weight:bold;margin-top:20px;color:#0a7a48;">Всього: '+total.toLocaleString('uk-UA')+' &#8372;</p>';
  html+='<div class="order-form"><h3>Оформити замовлення</h3>';
  html+='<label class="flabel">Ваше ім\'я</label><input type="text" class="field" id="oname" placeholder="Введіть ваше ім\'я">';
  html+='<label class="flabel">Телефон</label><input type="text" class="field" id="ophone" placeholder="(+380) 96 123 45 67">';
  html+='<button class="btn-order" id="obtn" onclick="sendOrder()">Підтвердити замовлення</button>';
  html+='<div class="result-ok" id="ook">&#10003; Замовлення прийнято і записано! Зателефонуємо найближчим часом.</div>';
  html+='<div class="result-err" id="oerr"></div>';
  html+='</div>';
  block.innerHTML=html;
}

function removeItem(i){
  var c=JSON.parse(localStorage.getItem('cart')||'[]');
  c.splice(i,1); localStorage.setItem('cart',JSON.stringify(c)); location.reload();
}

function sendOrder(){
  var name=document.getElementById('oname').value.trim();
  var phone=document.getElementById('ophone').value.trim();
  var err=document.getElementById('oerr');
  if(!name||!phone){err.textContent='Заповніть ім\'я та телефон';err.style.display='block';return;}
  err.style.display='none';

  // Читаємо кошик напряму з localStorage — безпечно, без проблем з лапками
  var cart=JSON.parse(localStorage.getItem('cart')||'[]');
  var total=0, items='';
  for(var i=0;i<cart.length;i++){
    total+=cart[i].price*cart[i].qty;
    items+=cart[i].name+' x'+cart[i].qty+'; ';
  }

  var btn=document.getElementById('obtn');
  btn.disabled=true; btn.textContent='Зберігаємо...';

  var xhr=new XMLHttpRequest();
  xhr.open('POST','save_order.php',true);
  xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  xhr.onload=function(){
    try { var res=JSON.parse(xhr.responseText); } catch(e){ var res={ok:true}; }
    if(res.ok){
      document.getElementById('ook').style.display='block';
      btn.style.display='none';
      localStorage.removeItem('cart');
      document.getElementById('cart-count').textContent=0;
    } else {
      btn.disabled=false; btn.textContent='Підтвердити замовлення';
      err.textContent=res.msg||'Помилка'; err.style.display='block';
    }
  };
  xhr.onerror=function(){
    btn.disabled=false; btn.textContent='Підтвердити замовлення';
  };
  xhr.send('name='+encodeURIComponent(name)+'&phone='+encodeURIComponent(phone)+'&total='+total+'&items='+encodeURIComponent(items));
}

document.querySelectorAll('a').forEach(function(a){
  a.addEventListener('click',function(e){
    var h=this.getAttribute('href');
    if(h&&h!='#'&&!h.startsWith('javascript')){
      e.preventDefault();document.body.classList.add('fade-out');
      setTimeout(function(){window.location.href=h;},220);
    }
  });
});
</script>
</body>
</html>
