<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header text-center">
                        <h4>Chats</h4>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($users as $user)
                            <a href="{{ route('chat', $user->id) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                                <span class="flex-grow-1">{{ $user->name }}</span>
                                @if($user->isOnline())
                                    <span class="badge rounded-pill bg-success">Online</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary">Offline</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>