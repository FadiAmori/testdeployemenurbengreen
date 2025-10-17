<x-dashboard::layout bodyClass="g-sidenav-show bg-gray-200" titlePage="Admin Dashboard" activePage="dashboard" :showSidebar="true">
    <x-dashboard::navbars.navs.auth titlePage="Admin Dashboard"></x-dashboard::navbars.navs.auth>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Users</p>
                                    <h5 class="font-weight-bolder">{{ number_format($stats['total_users']) }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                    <i class="material-icons opacity-10">group</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Blocked Users</p>
                                    <h5 class="font-weight-bolder">{{ number_format($stats['blocked_users']) }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                    <i class="material-icons opacity-10">lock</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Products</p>
                                    <h5 class="font-weight-bolder">{{ number_format($stats['total_products']) }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                    <i class="material-icons opacity-10">inventory</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Upcoming Events</p>
                                    <h5 class="font-weight-bolder">{{ number_format($stats['upcoming_events']) }}</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                    <i class="material-icons opacity-10">event</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h6>Recent Users</h6>
                        <p class="text-sm mb-0">
                            <span class="font-weight-bold">{{ count($recentUsers) }}</span> new users in the latest records
                        </p>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-group">
                            @forelse ($recentUsers as $recentUser)
                                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape icon-sm me-3 bg-gradient-primary shadow text-center">
                                            <i class="material-icons opacity-10">person</i>
                                        </div>
                                        <div>
                                            <h6 class="text-sm mb-0">{{ $recentUser->name }}</h6>
                                            <p class="text-xs text-secondary mb-0">{{ $recentUser->email }}</p>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <p class="text-xs text-secondary mb-0">Role</p>
                                        <span class="text-sm font-weight-bold text-capitalize">{{ $recentUser->role ?? 'user' }}</span>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item border-0 ps-0">No users found.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h6>Recent Orders</h6>
                        <p class="text-sm mb-0">Latest transactions placed in the store</p>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Order #</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Customer</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentOrders as $order)
                                        <tr>
                                            <td class="align-middle text-sm">#{{ $order->id }}</td>
                                            <td class="align-middle text-sm">{{ optional($order->user)->name ?? 'Guest' }}</td>
                                            <td class="align-middle text-sm">
                                                {{ $order->total_price ? number_format($order->total_price, 2) . ' $' : '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-secondary text-sm">No orders found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-dashboard::footers.auth></x-dashboard::footers.auth>
    </div>
</x-dashboard::layout>
