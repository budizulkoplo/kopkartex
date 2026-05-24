<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Drawer Menu</title>
  <style>
    body {
      margin: 0;
      font-family: sans-serif;
      padding-bottom: 80px; /* space for bottom bar */
    }

    .quickMenuBar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      height: calc(70px + env(safe-area-inset-bottom));
      background: #fff;
      box-shadow: 0 -10px 28px rgba(23, 33, 47, 0.1);
      display: flex;
      justify-content: space-around;
      align-items: center;
      z-index: 1001;
      padding: 0 10px env(safe-area-inset-bottom);
      border-top: 1px solid #e4edf3;
    }

    .quick-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      font-size: 12px;
      min-width: 52px;
      min-height: 48px;
      color: #6f7d8f;
      text-decoration: none;
      background: none;
      border: none;
      cursor: pointer;
      font-weight: 700;
    }

    .quick-item.active,
    .quick-item:hover {
      color: #0f6fcf;
    }

    .quick-item ion-icon {
      font-size: 22px;
      transition: transform 0.3s ease;
    }

    .menu-main {
      background: linear-gradient(135deg, #0b4f9a, #0f6fcf);
      color: white;
      width: 54px;
      height: 54px;
      padding: 0;
      display: inline-flex;
      flex-direction: row;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transform: translateY(-20%);
      box-shadow: 0 12px 22px rgba(15, 111, 207, 0.28);
      font-size: 18px;
    }

    .menu-main ion-icon {
      display: block;
      margin: 0;
      line-height: 1;
    }

    .logout {
      color: red !important;
    }

    .logout ion-icon {
      color: red !important;
    }

    .bottomMenuOverlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 30, 46, 0.34);
      z-index: 1000;
      display: none;
    }

    .bottomMenuOverlay.active {
      display: block;
    }

    .bottomMenuDrawer {
      position: fixed;
      left: 0;
      right: 0;
      bottom: -100%;
      background: #fff;
      transition: bottom 0.3s ease;
      z-index: 1001;
      padding-top: 40px;
      border-top-left-radius: 18px;
      border-top-right-radius: 18px;
      box-shadow: 0 -16px 40px rgba(23, 33, 47, 0.18);
      max-height: 90vh;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .bottomMenuDrawer.active {
      bottom: 0;
    }

    .drawerCloseArrow {
      position: absolute;
      top: -25px;
      left: 50%;
      transform: translateX(-50%);
      background: #ffffff;
      border: none;
      border-radius: 30px;
      padding: 6px 12px;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
      cursor: pointer;
      z-index: 1002;
      animation: pulseDown 1.5s infinite;
    }

    .drawerCloseArrow ion-icon {
      font-size: 20px;
      color: #0f6fcf;
    }

    @keyframes pulseDown {
      0%, 100% { transform: translateX(-50%) translateY(0); }
      50% { transform: translateX(-50%) translateY(5px); }
    }

    .drawerContent {
      overflow-y: auto;
      width: 100%;
      padding: 0 20px 30px;
      flex: 1;
      -webkit-overflow-scrolling: touch;
    }

    .bottomMenuGrid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 25px;
      max-width: 500px;
      margin: 0 auto;
    }

    .item {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-decoration: none;
      color: #333;
      font-size: 13px;
      font-weight: 700;
    }

    .item .col {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      text-align: center;
    }

    .item ion-icon {
      font-size: 26px;
      color: #526274;
    }

    .item.active ion-icon,
    .item.active .col {
      color: #0f6fcf;
    }

    .item.logout ion-icon,
    .item.logout .col {
      color: red !important;
    }

    @media (max-width: 390px) {
      .quick-item {
        min-width: 44px;
        font-size: 11px;
      }

      .quick-item ion-icon {
        font-size: 21px;
      }

      .menu-main {
        width: 50px;
        height: 50px;
      }

      .bottomMenuGrid {
        gap: 18px 14px;
      }
    }
  </style>
</head>
<body>

<!-- Bottom Quick Menu -->
<div class="quickMenuBar">
  <a href="/mobile/home" class="quick-item {{ request()->is('mobile/home') ? 'active' : '' }}">
    <ion-icon name="home-outline"></ion-icon>
    <span>Home</span>
  </a>
  <a href="/mobile/belanja" class="quick-item {{ request()->is('mobile/belanja') ? 'active' : '' }}">
    <ion-icon name="storefront-outline"></ion-icon>
    <span>Belanja</span>
  </a>

  <button class="quick-item menu-main" id="menuToggle">
    <ion-icon id="menuIcon" name="apps-outline"></ion-icon>
  </button>

  <a href="/mobile/belanja/history" class="quick-item {{ request()->is('mobile/belanja/history') ? 'active' : '' }}">
    <ion-icon name="document-text-outline"></ion-icon>
    <span>Riwayat</span>
  </a>

  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="all: unset; display: contents;">
    @csrf
    <button type="submit" class="quick-item logout">
      <ion-icon name="exit-outline"></ion-icon>
      <span>Logout</span>
    </button>
  </form>
</div>

<!-- Overlay -->
<div class="bottomMenuOverlay" id="menuOverlay"></div>

<!-- Drawer -->
<div class="bottomMenuDrawer" id="menuDrawer">
  <div class="drawerCloseArrow" id="drawerClose">
    <ion-icon class="text-primary" name="chevron-down-outline"></ion-icon>
  </div>
  <div class="drawerContent">
    <div class="bottomMenuGrid">
        @foreach($drawerMenus as $menu)
          <a href="{{ $menu->link }}" class="item {{ request()->is(ltrim($menu->link, '/')) ? 'active' : '' }}">
            <div class="col">
              <ion-icon name="{{ $menu->icon }}"></ion-icon>
              <strong>{{ $menu->namamenu }}</strong>
            </div>
          </a>
        @endforeach
      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="all: unset; display: contents;">
        @csrf
        <button type="submit" style="all: unset; cursor: pointer;" class="item logout">
          <div class="col">
            <ion-icon name="exit-outline"></ion-icon>
            <strong>Logout</strong>
          </div>
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Ionicons -->
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<!-- Script -->
<script>
  const toggle = document.getElementById('menuToggle');
  const drawer = document.getElementById('menuDrawer');
  const overlay = document.getElementById('menuOverlay');
  const closeBtn = document.getElementById('drawerClose');
  const menuIcon = document.getElementById('menuIcon');

  function openDrawer() {
    drawer.classList.add('active');
    overlay.classList.add('active');
    menuIcon.setAttribute('name', 'chevron-down-outline');
  }

  function closeDrawer() {
    drawer.classList.remove('active');
    overlay.classList.remove('active');
    menuIcon.setAttribute('name', 'apps-outline');
  }

  function toggleDrawer() {
    if (drawer.classList.contains('active')) {
      closeDrawer();
    } else {
      openDrawer();
    }
  }

  toggle.addEventListener('click', toggleDrawer);
  overlay.addEventListener('click', closeDrawer);
  closeBtn.addEventListener('click', closeDrawer);

  // Swipe-down gesture
  let startY = 0;
  drawer.addEventListener('touchstart', e => {
    startY = e.touches[0].clientY;
  });
  drawer.addEventListener('touchend', e => {
    const endY = e.changedTouches[0].clientY;
    if (endY - startY > 40) {
      closeDrawer();
    }
  });
</script>

</body>
</html>
