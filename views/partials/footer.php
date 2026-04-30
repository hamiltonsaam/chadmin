  </div></main>

  <footer class="footer">
    <span class="footer-copy"><?= h((string) cfg('app_name')) ?></span>
    <span>&copy; <?= date('Y') ?> Companies House</span>
  </footer>
</div>

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
</body>
</html>