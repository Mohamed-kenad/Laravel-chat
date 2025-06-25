<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat with {{ $receiver->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <!-- Chat Header -->
                    <div class="card-header d-flex align-items-center gap-3">
                        <!-- User Avatar -->
                         <a href="{{ route('users') }}" class="btn btn-sm"><-
                        </a>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white" style="width: 40px; height: 40px; font-size: 1.2rem;">
                            {{ strtoupper(substr($receiver->name, 0, 1)) }}
                        </span>
                    <div class="fw-bold d-flex align-items-center">
                        @if(Cache::has('user-is-online-' . $receiver->id))
                            <span style="display:inline-block;width:10px;height:10px;background:#28a745;border-radius:50%;margin-right:7px;border:1.5px solid #fff;"></span>
                        @else
                            <span style="display:inline-block;width:10px;height:10px;background:#adb5bd;border-radius:50%;margin-right:7px;border:1.5px solid #fff;"></span>
                        @endif
                        {{ $receiver->name }}
                        </div>
                        @if(Cache::has('user-is-online-' . $receiver->id))
                            <span class="badge bg-success d-flex">Online</span>
                        @else
                            <span class="badge bg-secondary d-flex">Offline</span>
                        @endif
                    </div>
                    <!-- Chat Messages -->
                    <div class="card-body" style="height: 400px; overflow-y: auto; background: #f8f9fa;">
                        <div id="chatbox" class="d-flex flex-column gap-2">
                            @foreach($messages as $msg)
                                @if($msg->sender_id == Auth::id())
                                    <div class="d-flex justify-content-start">
                                        <div>
                                            <div class="bg-primary text-white rounded px-3 py-2 mb-1" style="max-width: 350px; word-break: break-word;">
                                                {{ $msg->message }}
                                            </div>
                                            <div class="text-start text-muted small">{{ $msg->created_at->format('H:i') }}</div>
                                        </div>
                                    </div>
                                @else
                                    <!-- Received message (theirs, right) -->
                                    <div class="d-flex justify-content-end">
                                        <div>
                                            <div class="bg-secondary text-white rounded px-3 py-2 mb-1" style="max-width: 350px; word-break: break-word;">
                                                {{ $msg->message }}
                                            </div>
                                            <div class="text-end text-muted small">{{ $msg->created_at->format('H:i') }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <div id="typing-indicator" class="text-muted small" style="display: none;">{{ $receiver->name }} is typing...</div>
                    </div>
                    <!-- Message Input -->
                    <form id="message-form" action="{{ url('/chat/' . $receiver->id . '/send') }}" method="POST" class="card-footer d-flex align-items-center gap-2">
                        @csrf
                        <input id="message" type="text" name="message" class="form-control" placeholder="Type a message..." autocomplete="off" required>
                        <button type="submit" class="btn btn-primary">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>

        document.addEventListener('DOMContentLoaded', function() {

            function debounce(func, delay) {
                let timer;
                return function(...args) {
                    clearTimeout(timer);
                    timer = setTimeout(() => func.apply(this, args), delay);
                };
            }



            let receiverId = {{ $receiver->id }};
            let senderId = {{ Auth::id() }};
            let chatbox = document.getElementById('chatbox');
            let messageForm = document.getElementById('message-form');
            let messageInput = document.getElementById('message');
            let typingIndicator = document.getElementById('typing-indicator');

            
            fetch("/online",
            {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            });

            

            window.Echo.private("chat." + senderId)
                .listen("MessageSent", (event) => {

                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'd-flex justify-content-end';
                    messageDiv.innerHTML = `<div><div class='bg-secondary text-white rounded px-3 py-2 mb-1' style='max-width: 350px; word-break: break-word;'>${event.message.message}</div></div>`;
                    chatbox.appendChild(messageDiv);
                    chatbox.scrollTop = chatbox.scrollHeight;
                });

            
            let typingTimeout = null;

            window.Echo.private("typing." + receiverId)
                .listen("UserTyping", (e) => {

                    typingIndicator.style.display = 'block';
                    if (typingTimeout) clearTimeout(typingTimeout);
                    typingTimeout = setTimeout(() => {
                        typingIndicator.style.display = 'none';
                    }, 2000);
                });



          
            messageForm.addEventListener("submit", function(e){
                e.preventDefault();
                const message = messageInput.value.trim();
                if(message){
                    fetch("/chat/" + receiverId + "/send", {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ message: message })
                    });
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'd-flex justify-content-start';
                    messageDiv.innerHTML = `<div><div class='bg-primary text-white rounded px-3 py-2 mb-1' style='max-width: 350px; word-break: break-word;'>${message}</div></div>`;
                    chatbox.appendChild(messageDiv);
                    chatbox.scrollTop = chatbox.scrollHeight;
                    messageInput.value = '';
                }
            });




            const sendTyping = debounce(() => {
                fetch(`/chat/typing`, {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
            }, 1000); 
            messageInput.addEventListener('input', sendTyping);





            window.addEventListener('beforeunload', function() {
                fetch("/offline", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                });
            }); 


        });

   
    </script>
</body>
</html>
