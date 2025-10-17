<x-dashboard::layout bodyClass="g-sidenav-show bg-gray-200">
    <x-dashboard::navbars.sidebar activePage="maintenance" />
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-dashboard::navbars.navs.auth titlePage="Maintenance" />
        <div class="container-fluid py-4">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-0">Maintenance page placeholder.</p>
                </div>
            </div>
        </div>
    </main>
</x-dashboard::layout>
