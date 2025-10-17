<?php

namespace App\Http\Controllers;

use App\Models\Comente;
use App\Models\Statute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\CommentNotificationMail;

class ComenteController extends Controller
{
    public function store(Request $request, $statuteId)
    {
        $request->validate([
            'description' => 'required|string',
        ]);

        $statute = Statute::with('user')->findOrFail($statuteId);

        // Create the comment
        $comment = $statute->comentes()->create([
            'description' => $request->description,
            'user_id' => auth()->id(), // Save the user who made the comment
        ]);

        // Send email notification to the statute author if they exist and are different from commenter
        if ($statute->user && $statute->user->email && $statute->user_id !== auth()->id()) {
            try {
                Mail::to($statute->user->email)->send(
                    new CommentNotificationMail($statute, $comment, auth()->user())
                );
            } catch (\Exception $e) {
                // Log error but don't block the comment creation
                \Log::error('Failed to send comment notification: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Comment added successfully.');
    }

    public function update(Request $request, Comente $comente)
    {
        $request->validate([
            'description' => 'required|string',
        ]);

        $comente->description = $request->description;
        $comente->save();

        return redirect()->back()->with('success', 'Comment updated successfully.');
    }

    public function destroy(Comente $comente)
    {
        $comente->delete();
        return redirect()->back()->with('success', 'Comment deleted successfully.');
    }
}
