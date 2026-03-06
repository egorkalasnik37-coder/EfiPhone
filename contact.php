<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Контакти — EfiPhone</title>
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
  .site-header nav a.active{background-color:rgba(10,122,72,.1);color:#0a7a48;font-weight:700}
  .cart-btn{display:flex;align-items:center;gap:8px;background-color:#0a7a48;color:#fff;font-size:12px;font-weight:700;padding:9px 18px;border-radius:50px;text-decoration:none}
  .cart-btn:hover{background-color:#076038}
  .badge{background:#fff;color:#0a7a48;font-size:11px;font-weight:800;min-width:20px;height:20px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center}
  main{flex:1;padding-top:80px}
  .content{background-color:#fffde7;padding:30px 40px}
  .field{width:100%;padding:10px;margin-top:5px;border:2px solid #0a7a48;border-radius:8px;font-family:'Unbounded',sans-serif;font-size:13px;box-sizing:border-box;outline:none}
  .field:focus{border-color:#076038}
  .btn-send{padding:12px 30px;background-color:#0a7a48;color:white;border:none;border-radius:50px;font-size:13px;cursor:pointer;font-family:'Unbounded',sans-serif;margin-top:10px}
  .btn-send:hover{background-color:#076038}
  .btn-send:disabled{background-color:#999;cursor:wait}
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
    <a href="contact.php" class="active">Контакти</a>
  </nav>
  <a href="cart.php" class="cart-btn">&#128722; Кошик <span class="badge" id="cart-count">0</span></a>
</header>

<main>
<div class="content">
  <h2 style="color:#0a7a48;margin-bottom:24px;">Контакти</h2>
  <table width="100%" border="0"><tr>

    <td width="50%" valign="top" style="padding-right:30px;">
      <h3 style="color:#0a7a48;margin-bottom:16px;">Ми на зв'язку</h3>
      <p style="margin-bottom:12px;">&gt; <strong>Телефон:</strong><br>
      <span style="color:#555;font-size:13px;">+38 (096) 123-45-67</span></p>
      <p style="margin-bottom:12px;">&gt; <strong>Telegram:</strong><br>
      <span style="color:#555;font-size:13px;">@efiphone_store</span></p>
      <p style="margin-bottom:12px;">&gt; <strong>Email:</strong><br>
      <span style="color:#555;font-size:13px;">hello@efiphone.ua</span></p>
      <p style="margin-bottom:12px;">&gt; <strong>Адреса:</strong><br>
      <span style="color:#555;font-size:13px;">м. Кременчук, вул. Вадима Пугачова 14-Д</span></p>
      <p>&gt; <strong>Графік роботи:</strong><br>
      <span style="color:#555;font-size:13px;">Пн–Пт: 10:00 – 20:00<br>Сб-Нд: вихідний</span></p>
    </td>

    <td width="50%" valign="top">
      <h3 style="color:#0a7a48;margin-bottom:16px;">Написати нам</h3>
      <p style="margin-bottom:5px;font-size:13px;">Ваше ім'я</p>
      <input type="text" class="field" id="mname" placeholder="Тут можна написати ім'я">
      <p style="margin-bottom:5px;margin-top:15px;font-size:13px;">Телефон або Email</p>
      <input type="text" class="field" id="mcontact" placeholder="+380 або E-mail">
      <p style="margin-bottom:5px;margin-top:15px;font-size:13px;">Повідомлення</p>
      <textarea class="field" rows="5" id="mtext" placeholder="Ваше питання..."></textarea>
      <br>
      <button class="btn-send" id="sbtn" onclick="sendMessage()">Надіслати</button>
      <div class="result-ok"  id="mok">&#10003; Повідомлення записано! Відповімо найближчим часом.</div>
      <div class="result-err" id="merr"></div>
    </td>

  </tr></table>
</div>
</main>

<div class="footer">&#169; 2026 EfiPhone.com . Усі права захищено</div>

<script>
function sendMessage(){
  var name=document.getElementById('mname').value.trim();
  var contact=document.getElementById('mcontact').value.trim();
  var message=document.getElementById('mtext').value.trim();
  var err=document.getElementById('merr');
  if(!name||!contact||!message){err.textContent='Будь ласка, заповніть всі поля';err.style.display='block';return;}
  err.style.display='none';
  var btn=document.getElementById('sbtn');
  btn.disabled=true; btn.textContent='Зберігаємо...';
  var xhr=new XMLHttpRequest();
  xhr.open('POST','save_message.php',true);
  xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  xhr.onload=function(){
    var res=JSON.parse(xhr.responseText);
    if(res.ok){
      document.getElementById('mok').style.display='block';
      btn.style.display='none';
      document.getElementById('mname').value='';
      document.getElementById('mcontact').value='';
      document.getElementById('mtext').value='';
    } else {
      btn.disabled=false; btn.textContent='Надіслати';
      err.textContent=res.msg; err.style.display='block';
    }
  };
  xhr.send('name='+encodeURIComponent(name)+'&contact='+encodeURIComponent(contact)+'&message='+encodeURIComponent(message));
}

var cart=JSON.parse(localStorage.getItem('cart')||'[]'),t=0;
for(var i=0;i<cart.length;i++) t+=cart[i].qty;
document.getElementById('cart-count').textContent=t;

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
