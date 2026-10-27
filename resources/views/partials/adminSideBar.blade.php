<div class="side-header show">
    <button class="side-header-close"><i class="zmdi zmdi-close"></i></button>
    <!-- Side Header Inner Start -->
    <div class="side-header-inner custom-scroll">

        <nav class="side-header-menu" id="side-header-menu">
            <ul>
                {{-- A menu with one page is a plain link; menus with several pages open a list --}}
                <li><a href="{{route('admin')}}"><i class="fa fa-tachometer"></i> <span>Admin Dashboard</span></a></li>
                <li class="has-sub-menu"><a href="#"><i class="fa fa-tachometer"></i> <span> Manage Subscribers</span></a>
                    <ul class="side-header-sub-menu">
                        <li><a href="{{route('admin.subscribers.create')}}"><span>Add Subscribers</span></a></li>
                        <li><a href="{{route('admin.subscribers.index')}}"><span>Subscribers</span></a></li>
                    </ul>
                </li>

                <li class="has-sub-menu"><a href="#"><i class="fa fa-tags"></i> <span>Manage Categories</span></a>
                    <ul class="side-header-sub-menu">
                        <li><a href="{{route('manageCategories.create')}}"><span>Add Category</span></a></li>
                        <li><a href="{{route('manageCategories.index')}}"><span>Edit Category</span></a></li>
                    </ul>
                </li>

                <li class="has-sub-menu"><a href="#"><i class="fa fa-certificate"></i> <span>Manage Brands</span></a>
                    <ul class="side-header-sub-menu">
                        <li><a href="{{route('manageBrands.create')}}"><span>Add Brand</span></a></li>
                        <li><a href="{{route('manageBrands.index')}}"><span>Edit Brands</span></a></li>
                    </ul>
                </li>

                <li class="has-sub-menu"><a href="#"><i class="fa fa-shopping-bag"></i> <span>Manage Products</span></a>
                    <ul class="side-header-sub-menu">
                        <li><a href="{{route('manageProducts.create')}}"><span>Add Product</span></a></li>
                        <li><a href="{{route('manageProducts.index')}}"><span>Edit Product</span></a></li>
                        <li><a href="{{route('manageProducts.soldOut')}}"><span>Sold Out</span></a></li>
                    </ul>
                </li>

                <li class="has-sub-menu"><a href="#"><i class="fa fa-cogs"></i> <span>Manage Options</span></a>
                    <ul class="side-header-sub-menu">
                        <li><a href="{{route('manageOptions.create')}}"><span>Add Option</span></a></li>
                        <li><a href="{{route('manageOptions.index')}}"><span>Edit Option</span></a></li>
                    </ul>
                </li>
                <li class="has-sub-menu"><a href="#"><i class="fa fa-picture-o"></i> <span>Manage Banner</span></a>
                    <ul class="side-header-sub-menu">
                        <li><a href="{{route('manageBanner.create')}}"><span>Add Banner Image</span></a></li>
                        <li><a href="{{route('manageBanner.index')}}"><span>Edit Banner Image</span></a></li>
                    </ul>
                </li>
                <li class="has-sub-menu"><a href="#"><i class="fa fa-list-alt"></i> <span>Manage Plan</span></a>
                    <ul class="side-header-sub-menu">
                        <li><a href="{{route('managePlan.create')}}"><span>Add Plan</span></a></li>
                        <li><a href="{{route('managePlan.index')}}"><span>Edit Plan</span></a></li>
                    </ul>
                </li>
                <li class="has-sub-menu"><a href="#"><i class="fa fa-question-circle"></i> <span>Manage FAQs</span></a>
                    <ul class="side-header-sub-menu">
                        <li><a href="{{route("manageFaq.create")}}"><span>Add FAQ</span></a></li>
                        <li><a href="{{route("manageFaq.index")}}"><span>Edit FAQ</span></a></li>
                    </ul>
                </li>
                <li class="has-sub-menu"><a href="#"><i class="fa fa-shopping-cart"></i> <span>Manage Orders</span><span class="ad-menu-count" data-pending-count hidden></span></a>
                    <ul class="side-header-sub-menu">
                        <li><a href="{{route("admin.orders.pending")}}"><span>Pending Orders List</span><span class="ad-menu-count" data-pending-count hidden></span></a></li>
                        <li><a href="{{route("admin.orders.delivered")}}"><span>Deliverd Orders List</span></a></li>
                        <li><a href="{{route("admin.orders.cancelled")}}"><span>Cancelled Orders List</span></a></li>
                    </ul>
                </li>
                <li><a href="{{route("admin.customers.index")}}"><i class="fa fa-users"></i> <span>Customers</span></a></li>

                <li><a href="{{route('admin.specialOffers.index')}}"><i class="fa fa-star"></i> <span>Special Offers</span></a></li>

                <li class="has-sub-menu"><a href="#"><i class="fa fa-ticket"></i> <span>Manage Coupons</span></a>
                    <ul class="side-header-sub-menu">
                        <li><a href="{{route('admin.coupons.create')}}"><span>Add Coupon</span></a></li>
                        <li><a href="{{route('admin.coupons.index')}}"><span>Edit Coupons</span></a></li>
                    </ul>
                </li>

            </ul>
        </nav>

    </div><!-- Side Header Inner End -->
</div>
