


<nav class="navbar admin-navbar navbar-expand bg-white">
  <div class="container-fluid px-3 px-lg-4">
    <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="true" aria-label="Toggle sidebar">
      <span></span>
      <span></span>
      <span></span>
    </button>

    <form class="d-none d-md-flex ms-3 flex-grow-1" role="search" onsubmit="event.preventDefault();">
      <input class="form-control search-input" type="search" placeholder="Rechercher un produit, une vente, un client..." aria-label="Search">
    </form>

    <div class="navbar-actions ms-auto">
      <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
        <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
      </button>

      <div class="dropdown">
        <button class="icon-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
          <span class="notification-dot"></span>
          <i class="bi bi-bell" aria-hidden="true"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end notification-menu">
          <div class="dropdown-header fw-bold text-body">Notifications</div>
          <a class="dropdown-item" href="#">
            <span class="notification-title">Alerte stock bas sur Château Margaux</span>
            <span class="notification-time">Il y a 5 min</span>
          </a>
          <a class="dropdown-item" href="#">
            <span class="notification-title">Nouvelle vente effectuée (VNT-0042)</span>
            <span class="notification-time">Il y a 15 min</span>
          </a>
        </div>
      </div>

      <div class="dropdown">
        <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          @php
          $current_user = Auth::user();
          @endphp
          @if ($current_user)
          <img class="avatar-img avatar-sm" src="{{ asset('assets/images/avatar/avatar.jpg') }}" alt="{{ $current_user->name }}">
          <span class="profile-name d-none d-sm-inline">{{ $current_user->name }}</span>
          @endif
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Mon Profil</a></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Se déconnecter</button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>
