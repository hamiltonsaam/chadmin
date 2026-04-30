/* ============================================================
   script.js — Compliance Portal shared scripts
   ============================================================ */
(function () {
  "use strict";

  /* ── Theme toggle ───────────────────────────────────────── */
  const toggle = document.getElementById("themeToggle");

  function applyTheme(dark) {
    document.body.classList.toggle("dark", dark);
    if (toggle) {
      const icon = toggle.querySelector(".material-symbols-outlined");
      if (icon) icon.textContent = dark ? "light_mode" : "dark_mode";
      toggle.setAttribute("aria-label",
        dark ? "Switch to light mode" : "Switch to dark mode");
    }
  }

  // Initialise from sessionStorage or system preference
  try {
    const saved       = sessionStorage.getItem("theme");
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
    applyTheme(saved ? saved === "dark" : prefersDark);
  } catch (_) {}

  if (toggle) {
    toggle.addEventListener("click", () => {
      const nowDark = !document.body.classList.contains("dark");
      applyTheme(nowDark);
      try { sessionStorage.setItem("theme", nowDark ? "dark" : "light"); } catch (_) {}
    });
  }

  /* ── Auto-active sidebar link ───────────────────────────── */
  const current = location.pathname.split("/").pop() || "index.html";
  document.querySelectorAll(".sidebar-link, .company-sidebar-link").forEach((link) => {
    const href = (link.getAttribute("href") || "").split("/").pop();
    if (href && href === current) link.classList.add("active");
  });

  /* ── Sidebar drawer (hamburger) ─────────────────────────── */
  const hamburgerBtn  = document.getElementById("hamburgerBtn");
  const sidebarOverlay = document.getElementById("sidebarOverlay");
  // Support both .company-sidebar (filing/company pages) and .main-sidebar (dashboard)
  const sidebar = document.getElementById("companySidebar")
               || document.getElementById("mainSidebar")
               || document.querySelector(".company-sidebar")
               || document.querySelector(".sidebar");
  const closeBtn = document.getElementById("sidebarCloseBtn");

  function openSidebar() {
    if (!sidebar) return;
    sidebar.classList.add("open");
    if (sidebarOverlay) { sidebarOverlay.classList.add("open"); sidebarOverlay.removeAttribute("aria-hidden"); }
    if (hamburgerBtn)   hamburgerBtn.setAttribute("aria-expanded", "true");
    document.body.style.overflow = "hidden";
  }

  function closeSidebar() {
    if (!sidebar) return;
    sidebar.classList.remove("open");
    if (sidebarOverlay) { sidebarOverlay.classList.remove("open"); sidebarOverlay.setAttribute("aria-hidden", "true"); }
    if (hamburgerBtn)   hamburgerBtn.setAttribute("aria-expanded", "false");
    document.body.style.overflow = "";
  }

  if (hamburgerBtn)   hamburgerBtn.addEventListener("click", openSidebar);
  if (closeBtn)       closeBtn.addEventListener("click", closeSidebar);
  if (sidebarOverlay) sidebarOverlay.addEventListener("click", closeSidebar);

  // Close sidebar when a nav link is tapped (mobile UX)
  if (sidebar) {
    sidebar.querySelectorAll(".company-sidebar-link, .sidebar-link").forEach(link => {
      link.addEventListener("click", () => {
        if (window.innerWidth < 1024) closeSidebar();
      });
    });
  }

  // Close on Escape key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeSidebar();
  });

  /* ── Mobile search bar toggle ───────────────────────────── */
  const mobileSearchBtn   = document.getElementById("mobileSearchBtn");
  const mobileSearchBar   = document.getElementById("mobileSearchBar");
  const mobileSearchClose = document.getElementById("mobileSearchClose");
  const mobileSearchInput = document.getElementById("mobileSearchInput");

  function openMobileSearch() {
    if (!mobileSearchBar) return;
    mobileSearchBar.classList.add("open");
    mobileSearchBar.removeAttribute("aria-hidden");
    if (mobileSearchInput) mobileSearchInput.focus();
  }

  function closeMobileSearch() {
    if (!mobileSearchBar) return;
    mobileSearchBar.classList.remove("open");
    mobileSearchBar.setAttribute("aria-hidden", "true");
  }

  if (mobileSearchBtn)   mobileSearchBtn.addEventListener("click", openMobileSearch);
  if (mobileSearchClose) mobileSearchClose.addEventListener("click", closeMobileSearch);

  // Close mobile search on Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeMobileSearch();
  });

})();

/* ============================================================
   USER DASHBOARD — Modals & Company Search
   ============================================================ */
(function () {
  "use strict";

  /* ── Sample Companies House data ───────────────────────── */
  const CH_DATA = [
    { number:'09876543', name:'Acme Global Solutions Ltd.',     type:'Private Limited', status:'Active',      inc:'15 Mar 2010' },
    { number:'11223344', name:'Pinnacle Consulting LLP',        type:'LLP',             status:'Active',      inc:'02 Jul 2018' },
    { number:'05544332', name:'Oldstone Manufacturing Co.',     type:'Private Limited', status:'Dissolved',   inc:'20 Jan 2001' },
    { number:'13456789', name:'Nexus Technologies Ltd',         type:'Private Limited', status:'Liquidation', inc:'11 Nov 2020' },
    { number:'15566778', name:'Green Energy Ventures PLC',      type:'Public Limited',  status:'Active',      inc:'08 Apr 2022' },
    { number:'07123456', name:'Albion Digital Services Ltd',    type:'Private Limited', status:'Active',      inc:'30 Sep 2015' },
    { number:'03341122', name:'Northern Freight Solutions Ltd', type:'Private Limited', status:'Dormant',     inc:'14 Feb 2003' },
    { number:'10998877', name:'SkyBridge Capital LLP',          type:'LLP',             status:'Active',      inc:'21 Jun 2017' },
    { number:'06655443', name:'Meridian Property Group Ltd',    type:'Private Limited', status:'Active',      inc:'03 Oct 2008' },
    { number:'12004455', name:'BrightPath Education CIC',       type:'CIC',             status:'Active',      inc:'19 Jan 2019' },
  ];

  const BADGE_MAP = {
    'Active':      'badge-active',
    'Dissolved':   'badge-dissolved',
    'Liquidation': 'badge-liquidation',
    'Dormant':     'badge-dormant',
  };

  /* ── Modal open/close ───────────────────────────────────── */
  function openModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('open');
    if (id === 'modal-search') renderResults('', '');
  }

  function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('open');
  }

  document.querySelectorAll('[data-open-modal]').forEach(btn => {
    btn.addEventListener('click', () => openModal(btn.dataset.openModal));
  });

  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.dataset.closeModal));
  });

  document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', e => {
      if (e.target === backdrop) backdrop.classList.remove('open');
    });
  });

  /* ── Manual form submit ─────────────────────────────────── */
  const btnSubmitManual = document.getElementById('btn-submit-manual');
  if (btnSubmitManual) {
    btnSubmitManual.addEventListener('click', () => {
      const name   = (document.getElementById('m-name')   || {}).value?.trim();
      const number = (document.getElementById('m-number') || {}).value?.trim();
      if (!name || !number) return;
      alert(`Company "${name}" (${number}) added to your portfolio.`);
      closeModal('modal-manual');
      const form = document.getElementById('form-manual');
      if (form) form.reset();
    });
  }

  /* ── Search: live filter ────────────────────────────────── */
  const searchInput  = document.getElementById('ch-search-input');
  const searchStatus = document.getElementById('ch-search-status');

  function getQuery()  { return searchInput  ? searchInput.value  : ''; }
  function getStatus() { return searchStatus ? searchStatus.value : ''; }

  if (searchInput)  searchInput.addEventListener('input',  () => renderResults(getQuery(), getStatus()));
  if (searchStatus) searchStatus.addEventListener('change', () => renderResults(getQuery(), getStatus()));

  /* ── Render search results ──────────────────────────────── */
  function highlight(text, q) {
    if (!q) return text;
    const re = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return text.replace(re, '<mark style="background:rgba(0,51,102,0.12);color:inherit;border-radius:2px;padding:0 2px;">$1</mark>');
  }

  function renderResults(query, status) {
    const q      = query.toLowerCase();
    const tbody  = document.getElementById('ch-results-body');
    const empty  = document.getElementById('ch-empty');
    const table  = document.getElementById('ch-results-table');
    if (!tbody || !empty || !table) return;

    const filtered = CH_DATA.filter(c => {
      const matchQ = !q || c.name.toLowerCase().includes(q) || c.number.includes(q);
      const matchS = !status || c.status === status;
      return matchQ && matchS;
    });

    if (filtered.length === 0) {
      tbody.innerHTML      = '';
      table.style.display  = 'none';
      empty.style.display  = 'block';
      return;
    }

    table.style.display = '';
    empty.style.display = 'none';

    tbody.innerHTML = filtered.map((c, i) => `
      <tr class="${i % 2 === 1 ? 'row-alt' : ''}">
        <td class="td-mono">${c.number}</td>
        <td class="td-medium">${highlight(c.name, q)}</td>
        <td class="td-muted">${c.type}</td>
        <td><span class="badge ${BADGE_MAP[c.status] || 'badge-neutral'}">${c.status}</span></td>
        <td class="td-muted td-nowrap">${c.inc}</td>
        <td class="td-right">
          <button class="btn btn-primary btn-sm" data-add-number="${c.number}" data-add-name="${c.name}">
            <span class="material-symbols-outlined" style="font-size:15px">add</span> Add
          </button>
        </td>
      </tr>
    `).join('');

    tbody.querySelectorAll('[data-add-number]').forEach(btn => {
      btn.addEventListener('click', () => {
        alert(`Company "${btn.dataset.addName}" (${btn.dataset.addNumber}) added to your portfolio.`);
        closeModal('modal-search');
      });
    });
  }

})();
