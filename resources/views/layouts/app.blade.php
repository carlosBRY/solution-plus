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

    /* Table pagination controls (JS fallback) */
    .table-pagination-controls { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; padding: 0.75rem 0; }
    .table-pagination-controls .pagination-info { font-size: 0.8125rem; color: #6c757d; }
    .table-pagination-controls .btn-group .btn { font-size: 0.8125rem; padding: 0.25rem 0.625rem; }
    .table-pagination-controls .btn-group .btn.active { background-color: #0d6efd; border-color: #0d6efd; color: #fff; }
    .table-pagination-controls select.per-page-select { font-size: 0.8125rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; border: 1px solid #dee2e6; }

    /* Modern Bootstrap 5 Pagination Design */
    .pagination { margin-bottom: 0; gap: 0.25rem; }
    .pagination .page-item .page-link {
      border-radius: 0.375rem !important;
      border: 1px solid #dee2e6;
      color: #495057;
      font-weight: 500;
      font-size: 0.84rem;
      padding: 0.35rem 0.75rem;
      transition: all 0.15s ease-in-out;
    }
    .pagination .page-item.active .page-link {
      background-color: #0d6efd;
      border-color: #0d6efd;
      color: #ffffff;
      box-shadow: 0 2px 4px rgba(13, 110, 253, 0.25);
    }
    .pagination .page-item.disabled .page-link {
      background-color: #f8f9fa;
      color: #adb5bd;
      border-color: #dee2e6;
    }
    .pagination .page-item:not(.active):not(.disabled) .page-link:hover {
      background-color: #e9ecef;
      color: #0d6efd;
      border-color: #ced4da;
    }

    /* Password Eye Toggle Button styling */
    .btn-toggle-password {
      border-color: #dee2e6;
      color: #6c757d;
      z-index: 4;
    }
    .btn-toggle-password:hover,
    .btn-toggle-password:focus {
      background-color: #e9ecef;
      color: #212529;
      border-color: #ced4da;
      box-shadow: none;
    }

    /* Fix Bootstrap Modals on Mobile / iOS WebKit & Stacking Context */
    .modal { -webkit-overflow-scrolling: touch; }
    body.modal-open { overflow: hidden; }
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
    // 0. Correction Téléportation & Empilement des Modals Bootstrap (Multi-modals & Stacked z-index)
    // ═══════════════════════════════════════════════════════════
    // Téléporte/ré-append tout modal sous <body> lors de son ouverture afin que le dernier modal
    // ouvert (ex: confirmation) se situe physiquement après les autres dans le DOM.
    // Calcule également des z-index dynamiques pour garantir que le modal de confirmation s'affiche
    // au-dessus du modal de formulaire et de son backdrop.
    document.addEventListener('show.bs.modal', function(e) {
      const modal = e.target;
      if (modal) {
        document.body.appendChild(modal);
        const openModals = document.querySelectorAll('.modal.show');
        const modalIndex = openModals.length;
        const baseZIndex = 1050 + (modalIndex * 20);
        modal.style.setProperty('z-index', (baseZIndex + 10).toString(), 'important');
      }
    });

    document.addEventListener('shown.bs.modal', function(e) {
      const openModals = Array.from(document.querySelectorAll('.modal.show'));
      const backdrops = Array.from(document.querySelectorAll('.modal-backdrop'));

      backdrops.forEach(function(backdrop, index) {
        const backdropZ = 1050 + (index * 20);
        backdrop.style.setProperty('z-index', backdropZ.toString(), 'important');
      });

      openModals.forEach(function(modal, index) {
        const modalZ = 1055 + (index * 20);
        modal.style.setProperty('z-index', modalZ.toString(), 'important');
      });
    });

    document.addEventListener('hidden.bs.modal', function() {
      const openModals = document.querySelectorAll('.modal.show');
      if (openModals.length > 0) {
        document.body.classList.add('modal-open');
      }
    });

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
    // 2. Pagination dynamique des tableaux (fallback côté client)
    // ═══════════════════════════════════════════════════════════
    document.querySelectorAll('table.table-paginated').forEach(function(table) {
      // Ignorer si le tableau utilise déjà la pagination serveur de Laravel
      const parentSection = table.closest('.panel') || table.closest('.card') || table.parentNode;
      if (parentSection && parentSection.querySelector('.pagination, nav[aria-label*="pagination" i]')) {
        return;
      }

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

  {{-- ═══════════════════════════════════════════════════════════ --}}
  {{-- Modal Global de Confirmation avant Enregistrement         --}}
  {{-- ═══════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="confirmActionModal" tabindex="-1" aria-labelledby="confirmActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary bg-opacity-10 border-bottom-0">
          <h5 class="modal-title fw-bold" id="confirmActionModalLabel">
            <i class="bi bi-shield-check me-2 text-primary"></i>Confirmation
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body py-4">
          <p class="mb-0 fs-6" id="confirmActionMessage">Êtes-vous sûr de vouloir effectuer cette action ?</p>
        </div>
        <div class="modal-footer border-top-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i>Annuler
          </button>
          <button type="button" class="btn btn-primary fw-bold" id="confirmActionBtn">
            <i class="bi bi-check-lg me-1"></i>Confirmer
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('confirmActionModal');
    const confirmModal = new bootstrap.Modal(modalEl);
    const confirmMessage = document.getElementById('confirmActionMessage');
    const confirmBtn = document.getElementById('confirmActionBtn');
    let pendingForm = null;
    let pendingButton = null;

    // ─── Interception des boutons avec data-confirm ───
    document.addEventListener('click', function(e) {
      const btn = e.target.closest('[data-confirm]');
      if (!btn) return;

      const form = btn.closest('form');
      if (!form) return;

      e.preventDefault();
      e.stopImmediatePropagation();

      pendingForm = form;
      pendingButton = btn;
      confirmMessage.textContent = btn.dataset.confirm;

      // Adapter le style du bouton de confirmation selon le contexte
      const isDanger = btn.classList.contains('btn-danger') || btn.classList.contains('btn-outline-danger');
      confirmBtn.className = isDanger
        ? 'btn btn-danger fw-bold'
        : 'btn btn-primary fw-bold';
      confirmBtn.innerHTML = isDanger
        ? '<i class="bi bi-trash me-1"></i>Confirmer la suppression'
        : '<i class="bi bi-check-lg me-1"></i>Confirmer';

      confirmModal.show();
    });

    // ─── Confirmer et soumettre ───
    confirmBtn.addEventListener('click', function() {
      if (!pendingForm) return;

      confirmModal.hide();

      // Marquer le formulaire comme confirmé pour bypasser la re-interception
      pendingForm.dataset.confirmed = 'true';

      // Désactiver le bouton original + spinner
      if (pendingButton) {
        disableButton(pendingButton);
      }

      pendingForm.submit();
      pendingForm = null;
      pendingButton = null;
    });

    // ─── Protection double-clic sur TOUS les formulaires ───
    document.addEventListener('submit', function(e) {
      const form = e.target;
      if (!form || form.tagName !== 'FORM') return;

      // Ignorer les formulaires de recherche/filtre (GET)
      if (form.method.toUpperCase() === 'GET') return;

      // Trouver le bouton submit qui a déclenché la soumission
      const submitBtn = form.querySelector('[type="submit"]:focus, button[type="submit"]');

      // Si le bouton a un data-confirm et le formulaire n'est pas encore confirmé, bloquer
      if (submitBtn && submitBtn.dataset.confirm && form.dataset.confirmed !== 'true') {
        return; // Géré par le listener click ci-dessus
      }

      // Nettoyer le flag de confirmation
      delete form.dataset.confirmed;

      // Empêcher la double-soumission
      if (form.dataset.submitting === 'true') {
        e.preventDefault();
        return;
      }
      form.dataset.submitting = 'true';

      // Désactiver tous les boutons submit du formulaire
      form.querySelectorAll('[type="submit"]').forEach(function(btn) {
        disableButton(btn);
      });

      // Réactiver après 8s en cas d'erreur réseau
      setTimeout(function() {
        form.dataset.submitting = '';
        form.querySelectorAll('[type="submit"]').forEach(function(btn) {
          enableButton(btn);
        });
      }, 8000);
    });

    function disableButton(btn) {
      btn.disabled = true;
      btn.dataset.originalHtml = btn.innerHTML;
      const label = btn.textContent.trim() || 'Traitement';
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + label + '…';
    }

    function enableButton(btn) {
      btn.disabled = false;
      if (btn.dataset.originalHtml) {
        btn.innerHTML = btn.dataset.originalHtml;
        delete btn.dataset.originalHtml;
      }
    }
  });
  </script>
</body>
</html>

