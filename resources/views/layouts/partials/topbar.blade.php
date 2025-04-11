<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3">
    <div class="container-fluid">
        <button class="btn btn-link text-decoration-none d-none d-md-block" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarNotification" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell position-relative">
                        @php
                            $unreadAlerts = \App\Models\Alert::where('is_read', false)->count();
                        @endphp
                        @if($unreadAlerts > 0)
                            <span class="notification-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $unreadAlerts }}
                            </span>
                        @endif
                        </i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarNotification">
                        <h6 class="dropdown-header">Alerts Center</h6>
                        
                        @php
                            $recentAlerts = \App\Models\Alert::with('product')->latest()->take(5)->get();
                        @endphp
                        
                        @if($recentAlerts->count() > 0)
                            @foreach($recentAlerts as $alert)
                                <li>
                                    <a class="dropdown-item d-flex align-items-center {{ $alert->is_read ? 'text-muted' : 'fw-bold' }}" href="{{ route('alerts.index', ['type' => $alert->type]) }}">
                                        <div class="me-3">
                                            <div class="icon-circle bg-{{ $alert->type == 'info' ? 'primary' : ($alert->type == 'warning' ? 'warning' : 'danger') }}">
                                                <i class="fas fa-{{ $alert->type == 'info' ? 'info-circle' : ($alert->type == 'warning' ? 'exclamation-triangle' : 'exclamation-circle') }} text-white"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="small text-gray-500">{{ $alert->created_at->format('F d, Y') }}</div>
                                            <span>{{ \Illuminate\Support\Str::limit($alert->message, 50) }}</span>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        @else
                            <li><a class="dropdown-item text-center" href="#">No new alerts</a></li>
                        @endif
                        
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center small text-gray-500" href="{{ route('alerts.index') }}">Show All Alerts</a></li>
                    </ul>
                </li>
                
                <li class="nav-item dropdown ms-3">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-2 d-none d-lg-inline text-gray-600">{{ Auth::user()->name }}</span>
                        <img class="rounded-circle" width="32" height="32" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4e73df&color=ffffff">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user-circle fa-sm fa-fw me-2 text-gray-400"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cogs fa-sm fa-fw me-2 text-gray-400"></i> Settings</a></li>
                        <li><a class="dropdown-item" href="{{ route('activity-log.index') }}"><i class="fas fa-list fa-sm fa-fw me-2 text-gray-400"></i> Activity Log</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i> Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav> 