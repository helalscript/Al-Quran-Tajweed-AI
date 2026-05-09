</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dashboard</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>
    <div class="container mt-5">
        <!-- Success Message -->
        @if (session('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4>Welcome, {{ auth()->user()->name }} ({{ auth()->user()->email }})</h4>
                <div class="d-flex">
                    <a onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn btn-danger me-2">Logout</a>
                    <form action="{{ route('app.user.deleteAccount') }}" method="POST" class="d-inline">
                        @csrf
                        @method('GET')
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </form>
                </div>
            </div>
            <div class="card-body text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" fill="red"
                    class="bi bi-trash3" viewBox="0 0 16 16">
                    <path
                        d="M6 1v1H1v1h14V2h-5V1H6Zm7 3H3v10.5A1.5 1.5 0 0 0 4.5 16h7a1.5 1.5 0 0 0 1.5-1.5V4Zm-2.5 3a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-1 0v-5a.5.5 0 0 1 .5-.5ZM5.5 7a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-1 0v-5a.5.5 0 0 1 .5-.5Z" />
                </svg>

                <h5 class="mt-3">Are you sure you want to delete your account?</h5>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
        </div>
    </div>
    <form id="logout-form" action="{{ route('app.user.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</body>

</html>