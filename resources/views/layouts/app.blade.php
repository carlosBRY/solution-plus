<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Solution Plus</title>

  <!-- Template CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

  <!-- Tom Select CSS (Local pour déploiement hors-ligne) -->
  <link rel="stylesheet" href="{{ asset('assets/vendors/tom-select/tom-select.bootstrap5.min.css') }}">

  <style>
    /* Tom Select global overrides */
    .ts-wrapper { font-size: 0.875rem; }
    .ts-wrapper .ts-control { min-height: 38px; border-color: #dee2e6; }
    .ts-wrapper.focus .ts-control { border-color: #86b7fe; box-shadow: 0 0 0 0.2rem rgba(13,110,253,.15); }
    .ts-dropdown { font-size: 0.875rem; z-index: 9999 !important; }
    .ts-dropdown .option.active { background-color: #0d6efd; color: #fff; }
    .modal .ts-dropdown { z-index: 10060 !important; }

    /* Table pagination controls */
    .table-pagination-controls { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; padding: 0.75rem 0; }
    .table-pagination-controls .pagination-info { font-size: 0.8125rem; color: #6c757d; }
    .table-pagination-controls .btn-group .btn { font-size: 0.8125rem; padding: 0.25rem 0.625rem; }
    .table-pagination-controls .btn-group .btn.active { background-color: #0d6efd; border-color: #0d6efd; color: #fff; }
    .table-pagination-controls select.per-page-select { font-size: 0.8125rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; border: 1px solid #dee2e6; }
  </style>
</head>
<body>
  <div class="admin-shell">
    @include('layouts.partials.sidebar')

    <div class="admin-main">
      @include('layouts.partials.navbar')

      <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
          @if (isset($header))
            <header class="page-heading mb-4">
              {{ $header }}
            </header>
          @endif

          {{ $slot }}
        </div>
      </main>

      @include('layouts.partials.footer')
    </div>
  </div>

  <!-- Template JS -->
  <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/main.js') }}"></script>

  <!-- Tom Select JS (Local pour déploiement hors-ligne) -->
  <script src="{{ asset('assets/vendors/tom-select/tom-select.complete.min.js') }}"></script>

  <script>
  document.addEventListener('DOMContentLoaded', function() {

    // ═══════════════════════════════════════════════════════════
    // 1. Tom Select — Selects avec recherche
    // ═══════════════════════════════════════════════════════════
    function initTomSelect(container) {
      const selects = (container || document).querySelectorAll('select:not(.ts-ignore):not(.tomselected)');
      selects.forEach(function(el) {
        if (el.tomselect) return;
        if (el.closest('.ts-wrapper')) return;

        new TomSelect(el, {
          allowEmptyOption: true,
          sortField: { field: 'text', direction: 'asc' },
          plugins: el.multiple ? ['remove_button'] : [],
          maxOptions: 500,
          render: {
            no_results: function() {
              return '<div class="no-results p-2 text-muted text-center">Aucun résultat trouvé</div>';
            }
          }
        });
      });
    }

    initTomSelect(document);

    // Re-initialiser Tom Select quand un modal Bootstrap s'ouvre
    document.addEventListener('shown.bs.modal', function(e) {
      setTimeout(function() { initTomSelect(e.target); }, 100);
    });

    // Observer les nouveaux éléments (lignes dynamiques ajoutées en JS)
    const bodyObserver = new MutationObserver(function(mutations) {
      mutations.forEach(function(m) {
        m.addedNodes.forEach(function(node) {
          if (node.nodeType === 1) {
            initTomSelect(node);
          }
        });
      });
    });
    bodyObserver.observe(document.body, { childList: true, subtree: true });

    // ═══════════════════════════════════════════════════════════
    // 2. Pagination dynamique des tableaux
    // ═══════════════════════════════════════════════════════════
    document.querySelectorAll('table.table-paginated').forEach(function(table) {
      const tbody = table.querySelector('tbody');
      if (!tbody) return;

      const allRows = Array.from(tbody.querySelectorAll('tr'));
      if (allRows.length <= 10) return; // Pas besoin de pagination

      let perPage = 10;
      let currentPage = 1;

      // Créer le conteneur de pagination
      const paginationContainer = document.createElement('div');
      paginationContainer.className = 'table-pagination-controls';
      table.parentNode.insertBefore(paginationContainer, table.nextSibling);

      function render() {
        const totalPages = Math.ceil(allRows.length / perPage);
        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * perPage;
        const end = start + perPage;

        allRows.forEach(function(row, i) {
          row.style.display = (i >= start && i < end) ? '' : 'none';
        });

        // Info
        const showStart = allRows.length > 0 ? start + 1 : 0;
        const showEnd = Math.min(end, allRows.length);

        paginationContainer.innerHTML = `
          <div class="pagination-info">
            Affichage ${showStart}–${showEnd} sur ${allRows.length}
            &nbsp;|&nbsp;
            <select class="per-page-select">
              <option value="10" ${perPage===10?'selected':''}>10 / page</option>
              <option value="25" ${perPage===25?'selected':''}>25 / page</option>
              <option value="50" ${perPage===50?'selected':''}>50 / page</option>
              <option value="100" ${perPage===100?'selected':''}>100 / page</option>
            </select>
          </div>
          <div class="btn-group" role="group"></div>
        `;

        // Événement changement par page
        paginationContainer.querySelector('.per-page-select').addEventListener('change', function() {
          perPage = parseInt(this.value);
          currentPage = 1;
          render();
        });

        // Boutons de pagination
        const btnGroup = paginationContainer.querySelector('.btn-group');
        if (totalPages <= 1) return;

        // Précédent
        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'btn btn-outline-secondary';
        prevBtn.innerHTML = '&laquo;';
        prevBtn.disabled = currentPage === 1;
        prevBtn.addEventListener('click', function() { currentPage--; render(); });
        btnGroup.appendChild(prevBtn);

        // Pages visibles (max 7 boutons)
        let startPage = Math.max(1, currentPage - 3);
        let endPage = Math.min(totalPages, startPage + 6);
        if (endPage - startPage < 6) startPage = Math.max(1, endPage - 6);

        for (let p = startPage; p <= endPage; p++) {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'btn ' + (p === currentPage ? 'btn-primary active' : 'btn-outline-secondary');
          btn.textContent = p;
          btn.addEventListener('click', function() { currentPage = p; render(); });
          btnGroup.appendChild(btn);
        }

        // Suivant
        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'btn btn-outline-secondary';
        nextBtn.innerHTML = '&raquo;';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.addEventListener('click', function() { currentPage++; render(); });
        btnGroup.appendChild(nextBtn);
      }

      render();
    });

  });
  </script>
</body>
</html>

