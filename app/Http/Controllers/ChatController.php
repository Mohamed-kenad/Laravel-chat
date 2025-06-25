<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use App\Events\MessageSent;
use App\Events\UserTyping;  
use Illuminate\Support\Facades\Auth;


class ChatController extends Controller
{

    public function index()
    {
        $users = User::where('id', '!=', Auth::user()->id)->get();
        return view('users', compact('users'));
    }

    public function chat($receiverId)
    {
        $receiver = User::find($receiverId);
        $userId = Auth::user()->id;
        $messages = Message::where(function($query) use ($userId, $receiverId) {
            $query->where('sender_id', $userId)->where('receiver_id', $receiverId);
        })->orWhere(function($query) use ($userId, $receiverId) {
            $query->where('sender_id', $receiverId)->where('receiver_id', $userId);
        })->orderBy('created_at')->get();
        return view('chat', compact('receiver', 'messages'));
    }

    public function sendMessage(Request $request, $receiverId)
    {
       $message = Message::create([
        'sender_id' => Auth::id(),
        'receiver_id' => $receiverId,
        'message' => $request->message
       ]);

    broadcast(new MessageSent($message))->toOthers();
    return response()->json(['status' => 'success']);
    }

    public function typing(){
        
    broadcast(new UserTyping(Auth::id()))->toOthers();
    return response()->json(['status' => 'success']);

    }
    
    public function isOnline(){
        Cache::put('user-is-online-'. Auth::id(), true, now()->addMinutes(5));
        return response()->json(['status' => 'success']);
    }

    public function isOffline(){
        Cache::forget('user-is-online-'. Auth::id());
        return response()->json(['status' => 'success']);
    }
    

}
