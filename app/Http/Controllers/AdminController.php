<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;

class AdminController extends Controller
{
    public function users()
    {
        return response()->json(
            User::select('id', 'name', 'role', 'email')->get()
        );
    }

    public function posts()
    {
        return response()->json(
            Post::with('user')->select('id', 'user_id', 'content')->get()
        );
    }

    public function deletePost($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return response()->json(['success' => true]);
    }
    public function stats()
    {
        return response()->json([
            'total_users'    => \App\Models\User::count(),
            'total_posts'    => \App\Models\Post::count(),
            'total_comments' => \App\Models\Comment::count(),
            'total_likes'    => \App\Models\Like::count(),
            'total_messages' => \App\Models\Message::count(),

            'posts_by_city' => \App\Models\Post::with('city')
                ->selectRaw('city_id, count(*) as total')
                ->groupBy('city_id')
                ->get()
                ->map(fn($p) => [
                    'city'  => $p->city->name,
                    'total' => $p->total,
                ]),

            'posts_by_category' => \App\Models\Post::with('category')
                ->selectRaw('category_id, count(*) as total')
                ->groupBy('category_id')
                ->get()
                ->map(fn($p) => [
                    'category' => $p->category->name,
                    'total'    => $p->total,
                ]),

            'users_by_role' => \App\Models\User::selectRaw('role, count(*) as total')
                ->groupBy('role')
                ->get(),
        ]);
    }
}
