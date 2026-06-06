<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Post::with(['user', 'city', 'category'])
                ->when($request->city_id,     fn($q) => $q->where('city_id',     $request->city_id))
                ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
                ->latest()
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_id'     => 'required|exists:cities,id',
            'category_id' => 'required|exists:categories,id',
            'content'     => 'required|string',
            'media'       => 'nullable|array',
            'media.*'     => 'image|mimes:jpg,jpeg,png,webp|max:5048',
        ]);

        $mediaPaths = [];

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('posts', 'public');
                $mediaPaths[] = asset('storage/' . $path);
            }
        }

        $post = Post::create([
            'user_id'     => $request->user()->id,
            'city_id'     => $validated['city_id'],
            'category_id' => $validated['category_id'],
            'content'     => $validated['content'],
            'media'       => $mediaPaths,
        ]);

        return response()->json($post->load(['city', 'category']), 201);
    }

    public function show($id)
    {
        $post = Post::with(['comments.user', 'likes'])->findOrFail($id);
        return response()->json($post);
    }

    public function like(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $already = Like::where('post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($already) {
            return response()->json(['message' => 'Already liked'], 409);
        }

        Like::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
        ]);

        $post->increment('likes_count');

        return response()->json([
            'success'     => true,
            'likes_count' => $post->fresh()->likes_count,
        ]);
    }

    public function comment(Request $request, $id)
    {
        $request->validate(['content' => 'required|string']);

        $post = Post::findOrFail($id);

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        $post->increment('comments_count');

        return response()->json($comment, 201);
    }

    // Add this new method to get comments
    public function getComments($id)
    {
        try {
            $post = Post::findOrFail($id);
            $comments = Comment::where('post_id', $post->id)
                ->with('user')
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $comments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $post->update($request->only(['content', 'media']));
        return response()->json($post);
    }

    public function destroy(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $post->delete();
        return response()->json(['success' => true]);
    }
}
