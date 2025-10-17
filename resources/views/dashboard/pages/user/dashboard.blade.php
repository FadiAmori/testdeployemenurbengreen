<x-dashboard::layout bodyClass="bg-gray-200" titlePage="My Dashboard" activePage="user-dashboard" :showSidebar="false">
    <x-dashboard::navbars.navs.auth titlePage="My Dashboard"></x-dashboard::navbars.navs.auth>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="material-icons text-success mb-2" style="font-size: 2.5rem;">favorite</i>
                        <h6>My Favorites</h6>
                        <p class="text-lg mb-0">{{ $favoritesCount }}</p>
                        <a href="{{ route('front.favorites.index') }}" class="btn btn-sm btn-success mt-3">View Favorites</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="material-icons text-info mb-2" style="font-size: 2.5rem;">shopping_bag</i>
                        <h6>My Orders</h6>
                        <p class="text-lg mb-0">{{ $ordersCount }}</p>
                        <a href="{{ route('front.shop', [], false) }}" class="btn btn-sm btn-info mt-3">Go to Shop</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="material-icons text-warning mb-2" style="font-size: 2.5rem;">event_available</i>
                        <h6>Upcoming Events</h6>
                        <p class="text-lg mb-0">{{ $upcomingEvents->count() }}</p>
                        <a href="{{ route('front.event') }}" class="btn btn-sm btn-warning mt-3">Browse Events</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h6>Next Events</h6>
                        <p class="text-sm mb-0">Stay engaged with the community</p>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @forelse ($upcomingEvents as $event)
                                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                    <div>
                                        <h6 class="text-sm mb-1">{{ $event->title }}</h6>
                                        <p class="text-xs text-secondary mb-0">
                                            {{ $event->event_date->format('M d, Y H:i') }} &bull; {{ $event->location }}
                                        </p>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <a href="{{ route('front.event.show', $event) }}" class="btn btn-outline-success btn-sm">Details</a>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item border-0 ps-0">No upcoming events yet. Check back soon!</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h6>Quick Links</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('profile') }}" class="list-group-item list-group-item-action">Update my profile</a>
                            <a href="{{ route('front.notifications.index') }}" class="list-group-item list-group-item-action">My notifications</a>
                            <a href="{{ route('front.cart', [], false) }}" class="list-group-item list-group-item-action">My cart</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-dashboard::footers.auth></x-dashboard::footers.auth>
    </div>
</x-dashboard::layout>
