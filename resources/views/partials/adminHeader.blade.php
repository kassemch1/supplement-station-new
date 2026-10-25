<div class="header-section">
    <div class="container-fluid">
        <div class="row justify-content-between align-items-center">

            <!-- Header Logo (Header Left) Start -->
            <div class="header-logo col-auto">
                <h3>Supplement Station</h3>
            </div><!-- Header Logo (Header Left) End -->

            <!-- Header Right Start -->
            <div class="header-right flex-grow-1 col-auto">
                <div class="row justify-content-between align-items-center">

                    <!-- Side Header Toggle & Search Start -->
                    <div class="col-auto">
                        <div class="row align-items-center">

                            <!--Side Header Toggle-->
                            <div class="col-auto"><button class="side-header-toggle" aria-label="Show or hide the menu"><i class="zmdi zmdi-menu"></i></button></div>

                        </div>
                    </div><!-- Side Header Toggle & Search End -->

                    <!-- Header Notifications Area Start -->
                    <div class="col-auto ad-header-tools">

                        {{-- new orders: count, latest pending orders, sound and desktop alerts (admin-ui.js) --}}
                        <div class="ad-bell" id="adBell">
                            <button type="button" class="ad-icon-btn" id="adBellBtn" aria-label="Pending orders" aria-expanded="false">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                <span class="ad-bell__count" id="adBellCount" hidden>0</span>
                            </button>
                            <div class="ad-bell__menu" id="adBellMenu" hidden>
                                <div class="ad-bell__head">
                                    <strong>Pending orders</strong>
                                    <a href="{{ route('admin.orders.pending') }}">See all</a>
                                </div>
                                <div class="ad-bell__list" id="adBellList">
                                    <p class="ad-bell__empty">Loading…</p>
                                </div>
                                <div class="ad-bell__foot">
                                    <label class="ad-switch">
                                        <input type="checkbox" id="adSoundToggle" checked>
                                        <span class="ad-switch__track"></span>
                                        <span>Sound for new orders</span>
                                    </label>
                                    <button type="button" class="ad-link-btn" id="adSoundTest">Test sound</button>
                                    <button type="button" class="ad-link-btn" id="adDesktopAlerts" hidden>Turn on desktop alerts</button>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="ad-icon-btn" id="adThemeToggle" aria-label="Switch between dark and light mode" title="Dark / light mode">
                            <svg class="ad-ico-sun" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                            <svg class="ad-ico-moon" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                        </button>

                        <form id="logout-form" action="{{route('logout')}}" method="post">
                            @csrf
                            <a class="button button-danger" style="color: white" onclick="logout()">logout</a>
                        </form>

                    </div><!-- Header Notifications Area End -->

                </div>
            </div><!-- Header Right End -->

        </div>
    </div>
</div>
<script>
    function logout(){
        document.getElementById('logout-form').submit();
    }
</script>
