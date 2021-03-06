<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\User;
use App\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

class PostsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    //For post create
    public function create()
    {
        return view('posts/create');
    }

    //For post store
    public function store()
    {
        $data = request()->validate([
            'caption' => 'required',
            'image' => ['required', 'image'],
        ]);

        $imagePath = request('image')->store('uploads', 'public'); // Store image to storage upload directory and Returns file path

        $image = Image::make(public_path("storage/{$imagePath}"))->fit(1200, 1200);
        $image->save();

        $user = Auth::user();
        $user->posts()->create([
            'caption' => $data['caption'],
            'image' => $imagePath,
        ]);

        return redirect('/profile/' . $user->id);
    }

    public function show(\App\Post $post)
    {
        $follows = (Auth::user()) ? Auth::user()->following->contains($post->user->profile) : false;
        return view("posts/show", [
            'post' => $post,
            'follows' => $follows,
        ]);
    }

    public function index()
    {
        $users = Auth::user()->following()->pluck('profiles.user_id');
        //$posts = Post::whereIn('user_id', $users)->orderBy('created_at', 'DESC')->get();
        //$posts = Post::whereIn('user_id', $users)->latest()->get();

        //It will pass user info along with their post
//        $posts = Post::whereIn('user_id', $users)->with('user')->latest()->get();
        //Paginating
        $posts = Post::whereIn('user_id', $users)->with('user')->latest()->paginate(5);


        return view('posts.index', compact('posts'));
    }
}
