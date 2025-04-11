<!-- Sales Menu -->
<li class="nav-item {{ Request::is('sales*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('sales*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-shopping-cart"></i>
        <p>
            Sales
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('sales.index') }}" class="nav-link {{ Request::is('sales') || Request::is('sales/index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>All Sales</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('sales.create') }}" class="nav-link {{ Request::is('sales/create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Add Sale</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('payments.index') }}" class="nav-link {{ Request::is('payments*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Payments</p>
            </a>
        </li>
    </ul>
</li> 