<?php

namespace App\Http\Controllers;

use App\Chat;
use App\Events\MessageSend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{

    public function index()
    {
        $messages = Chat::with('user')->get();

        return response()->json($messages);
    }

    public function store()
    {
        $message = Auth::user()->messages()->create(['message'=>request('message')]);

        $message = $message->load('user');

        broadcast(new MessageSend($message))->toOthers();

        return $message->toJson();
    }


}
