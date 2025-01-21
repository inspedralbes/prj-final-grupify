<!DOCTYPE html>
<html lang="en">
<head>
<head>
    <!-- ... otros meta tags ... -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- ... resto del head ... -->
</head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: rgb(0, 173, 238);
            --primary-hover: rgb(0, 153, 218);
            --background-color: #f2f2f2;
            --card-background-color: #ffffff;
        }

        body {
            background-color: var(--background-color);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 0; 
        }

        .navbar-brand, .nav-link {
            color: white !important;
        }

        .navbar-brand h1 {
            font-weight: 700; 
            font-size: 2rem;
            margin: 0;
        }

        .navbar-brand:hover, .nav-link:hover {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .display-4 {
            font-size: 2.5rem;
            font-weight: 700; 
            margin-bottom: 1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .content-wrapper {
            min-height: calc(100vh - 56px);
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            background-color: var(--card-background-color);
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .logout-btn {
            color: white !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.2rem 1rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s;
        }

        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <h1>Panell Administrador</h1>
            </a>
            <div class="ms-auto">
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
        @csrf
        <a href="#" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i>
            Tancar Sessió
        </a>
    </form>
</div>
        </div>
    </nav>

    <!-- Main content -->
    <main class="content-wrapper">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function handleLogout() {
        // Realizar una solicitud al servidor para cerrar sesión
        fetch('/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(response => {
            if (response.ok) {
                // Redirigir al inicio
                window.location.href = '/';
            } else {
                alert('Error al cerrar sesión.');
            }
        }).catch(error => {
            console.error('Error:', error);
        });
    }
</script>

</body>
</html>