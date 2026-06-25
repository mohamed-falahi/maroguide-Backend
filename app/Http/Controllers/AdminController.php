<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use App\Models\Post;
use App\Models\City;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function __construct()
    {
        // No middleware here - defined in routes
    }

    public function users()
    {
        return response()->json(
            User::select('id', 'name', 'role', 'email', 'created_at')->get()
        );
    }

    public function posts()
    {
        return response()->json(
            Post::with('user', 'city', 'category')
                ->withCount(['likes', 'comments'])
                ->select('id', 'user_id', 'city_id', 'category_id', 'content', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function deletePost($id)
    {
        $post = Post::findOrFail($id);

        if ($post->media) {
            $media = is_array($post->media) ? $post->media : json_decode($post->media, true);
            if ($media) {
                foreach ($media as $file) {
                    if (Storage::disk('public')->exists($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }
        }

        $post->delete();
        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully'
        ]);
    }

    // ADD THIS METHOD FOR DELETING USERS
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Prevent admin from deleting themselves
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account'
            ], 422);
        }

        // Delete user's avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Delete user's cover image if exists
        if ($user->cover_image && Storage::disk('public')->exists($user->cover_image)) {
            Storage::disk('public')->delete($user->cover_image);
        }

        // Delete user's posts and their media
        $posts = $user->posts;
        foreach ($posts as $post) {
            if ($post->media) {
                $media = is_array($post->media) ? $post->media : json_decode($post->media, true);
                if ($media) {
                    foreach ($media as $file) {
                        if (Storage::disk('public')->exists($file)) {
                            Storage::disk('public')->delete($file);
                        }
                    }
                }
            }
            $post->delete();
        }

        // Delete user's tokens
        $user->tokens()->delete();

        // Delete user
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    public function stats()
    {
        return response()->json([
            'total_users'    => User::count(),
            'total_posts'    => Post::count(),
            'total_comments' => Comment::count(),
            'total_likes'    => Like::count(),
            'total_messages' => Message::count(),

            'posts_by_city' => Post::with('city')
                ->selectRaw('city_id, count(*) as total')
                ->groupBy('city_id')
                ->get()
                ->map(fn($p) => [
                    'city'  => $p->city?->name ?? 'Unknown',
                    'total' => $p->total,
                ]),

            'posts_by_category' => Post::with('category')
                ->selectRaw('category_id, count(*) as total')
                ->groupBy('category_id')
                ->get()
                ->map(fn($p) => [
                    'category' => $p->category?->name ?? 'Unknown',
                    'total'    => $p->total,
                ]),

            'users_by_role' => User::selectRaw('role, count(*) as total')
                ->groupBy('role')
                ->get(),
        ]);
    }

    // =============================================
    // CITY MANAGEMENT METHODS
    // =============================================

    public function cities()
    {
        $cities = City::orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    public function storeCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:cities',
            'region' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $city = new City();
        $city->name = $request->name;
        $city->region = $request->region;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('cities', 'public');
            $city->image = $path;
        }

        $city->save();

        return response()->json([
            'success' => true,
            'message' => 'City created successfully',
            'data' => $city
        ]);
    }

    public function updateCity(Request $request, $id)
    {
        $city = City::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:cities,name,' . $id,
            'region' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $city->name = $request->name;
        $city->region = $request->region;

        if ($request->hasFile('image')) {
            if ($city->image && Storage::disk('public')->exists($city->image)) {
                Storage::disk('public')->delete($city->image);
            }
            $path = $request->file('image')->store('cities', 'public');
            $city->image = $path;
        }

        $city->save();

        return response()->json([
            'success' => true,
            'message' => 'City updated successfully',
            'data' => $city
        ]);
    }

    public function deleteCity($id)
    {
        $city = City::findOrFail($id);

        if ($city->posts()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete city with existing posts'
            ], 422);
        }

        if ($city->image && Storage::disk('public')->exists($city->image)) {
            Storage::disk('public')->delete($city->image);
        }

        $city->delete();

        return response()->json([
            'success' => true,
            'message' => 'City deleted successfully'
        ]);
    }

    public function categories()
    {
        $categories = Category::orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $category = Category::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category
        ]);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);

        if ($category->posts()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with existing posts'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
