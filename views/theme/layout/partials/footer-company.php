
  <footer class="footer">
    <span class="footer-copy"><?= $pageTitle ?></span>
    <span>   
      <a href="/chadmin/views/theme/layout/privacy.php"
      style="color:var(--on-surface-variant);">Privacy</a>
      &nbsp;·&nbsp;
     <a href="/chadmin/views/theme/layout/terms.php"
      style="color:var(--on-surface-variant);">Terms</a>
     &copy; <?= date('Y') ?> 
     HAMILTONN LTD
    </span>
  </footer>

<script>
(function(){
  const btn = document.getElementById('themeToggle');
  const html = document.documentElement;
  const body = document.body;
  let dark = body.classList.contains('dark');
  btn && btn.addEventListener('click', () => {
    dark = !dark;
    body.classList.toggle('dark', dark);
    btn.querySelector('.material-symbols-outlined').textContent = dark ? 'light_mode' : 'dark_mode';
  });
})();
</script>
<script src="views/theme/assets/js/script.js" defer></script>

