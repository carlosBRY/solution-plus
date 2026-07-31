<div class="sidebar-backdrop" data-sidebar-close></div>

<aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
  <div class="sidebar-header">
    <a class="brand-mark" href="{{ route('dashboard') }}" aria-label="Cave Prestige d'Or">
      <span class="brand-icon"><i class="bi bi-cup-straw" aria-hidden="true"></i></span>
      <span class="brand-copy">
        <span class="brand-title">{{ $parametre->nom_cave }}</span>
        <span class="brand-subtitle">Gestion Commerciale</span>
      </span>
    </a>
  </div>

  <nav class="sidebar-nav">
    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}">
      <span class="nav-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
      <span class="nav-text">Tableau de bord</span>
    </a>

    @can('gérer-ventes')
    <a class="nav-link {{ request()->routeIs('ventes.*') ? 'active' : '' }}" href="{{ route('ventes.index') }}">
      <span class="nav-icon"><i class="bi bi-cart-check" aria-hidden="true"></i></span>
      <span class="nav-text">Ventes & Caisse</span>
    </a>
    @endcan

    @can('gérer-produits')
    <a class="nav-link {{ request()->routeIs('produits.*') ? 'active' : '' }}" href="{{ route('produits.index') }}">
      <span class="nav-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
      <span class="nav-text">Produits & Crus</span>
    </a>
    @endcan

    @can('gérer-categories')
    <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
      <span class="nav-icon"><i class="bi bi-tags" aria-hidden="true"></i></span>
      <span class="nav-text">Catégories</span>
    </a>
    @endcan

    @can('gérer-stocks')
    <a class="nav-link {{ request()->routeIs('stocks.index') ? 'active' : '' }}" href="{{ route('stocks.index') }}">
      <span class="nav-icon"><i class="bi bi-layers" aria-hidden="true"></i></span>
      <span class="nav-text">Gestion du Stock</span>
    </a>
    <a class="nav-link {{ request()->routeIs('stocks.mouvements') ? 'active' : '' }}" href="{{ route('stocks.mouvements') }}" style="padding-left: 2.8rem; font-size: 0.85rem;">
      <span class="nav-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
      <span class="nav-text">Mouvements de Stock</span>
    </a>
    <a class="nav-link {{ request()->routeIs('deteriorations.*') ? 'active' : '' }}" href="{{ route('deteriorations.index') }}">
      <span class="nav-icon"><i class="bi bi-slash-circle" aria-hidden="true"></i></span>
      <span class="nav-text">Détériorations & Casses</span>
    </a>
    @endcan

    @can('gérer-approvisionnements')
    <a class="nav-link {{ request()->routeIs('approvisionnements.*') ? 'active' : '' }}" href="{{ route('approvisionnements.index') }}">
      <span class="nav-icon"><i class="bi bi-truck" aria-hidden="true"></i></span>
      <span class="nav-text">Approvisionnements</span>
    </a>
    @endcan

    @can('gérer-caisses')
    <a class="nav-link {{ request()->routeIs('comptes.*') ? 'active' : '' }}" href="{{ route('comptes.index') }}">
      <span class="nav-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
      <span class="nav-text">Caisse Principale</span>
    </a>
    <a class="nav-link {{ request()->routeIs('caisses.*') ? 'active' : '' }}" href="{{ route('caisses.index') }}" style="padding-left: 2.8rem; font-size: 0.85rem;">
      <span class="nav-icon"><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
      <span class="nav-text">Sessions Caisses</span>
    </a>
    @endcan

    @can('gérer-clients')
    <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
      <span class="nav-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
      <span class="nav-text">Clients & Crédits</span>
    </a>
    @endcan

    @can('gérer-fournisseurs')
    <a class="nav-link {{ request()->routeIs('fournisseurs.*') ? 'active' : '' }}" href="{{ route('fournisseurs.index') }}">
      <span class="nav-icon"><i class="bi bi-building" aria-hidden="true"></i></span>
      <span class="nav-text">Fournisseurs</span>
    </a>
    @endcan

    @can('gérer-depenses')
    <a class="nav-link {{ request()->routeIs('depenses.*') ? 'active' : '' }}" href="{{ route('depenses.index') }}">
      <span class="nav-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span>
      <span class="nav-text">Dépenses</span>
    </a>
    @endcan

    @can('gérer-inventaires')
    <a class="nav-link {{ request()->routeIs('inventaires.*') ? 'active' : '' }}" href="{{ route('inventaires.index') }}">
      <span class="nav-icon"><i class="bi bi-clipboard-check" aria-hidden="true"></i></span>
      <span class="nav-text">Inventaires</span>
    </a>
    @endcan

    @can('gérer-parametres')
    <a class="nav-link {{ request()->routeIs('parametres.index') ? 'active' : '' }}" href="{{ route('parametres.index') }}">
      <span class="nav-icon"><i class="bi bi-gear" aria-hidden="true"></i></span>
      <span class="nav-text">Paramètres</span>
    </a>
    <a class="nav-link {{ request()->routeIs('parametres.conditionnements') ? 'active' : '' }}" href="{{ route('parametres.conditionnements') }}" style="padding-left: 2.8rem; font-size: 0.85rem;">
      <span class="nav-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
      <span class="nav-text">Conditionnements</span>
    </a>
    @endcan

    @can('gérer-utilisateurs')
    <a class="nav-link {{ request()->routeIs('parametres.users.*') ? 'active' : '' }}" href="{{ route('parametres.users.index') }}" style="padding-left: 2.8rem; font-size: 0.85rem;">
      <span class="nav-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
      <span class="nav-text">Utilisateurs</span>
    </a>
    @endcan
  </nav>

  <div class="sidebar-user">
    <img class="avatar-img avatar-md sidebar-user-avatar" src="{{ asset('assets/images/avatar/avatar.jpg') }}" alt="{{ Auth::user()->name }}">
    <strong>{{ Auth::user()->name }}</strong>
    <span class="badge bg-success mt-1">{{ Auth::user()->roles->first()?->name ?? 'Utilisateur' }}</span>
  </div>

  <div class="sidebar-footer">
    <span class="status-dot"></span>
    <span class="sidebar-footer-text">{{ $parametre->nom_cave }} v1.0</span>
  </div>
</aside>
