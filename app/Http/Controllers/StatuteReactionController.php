<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Statute;
use App\Models\StatuteReaction;
use Illuminate\Http\JsonResponse;

class StatuteReactionController extends Controller
{
    /**
     * Toggle a reaction (like/dislike) for the authenticated user on a statute.
     *
     * Body params: { "reaction": "like" | "dislike" }
     */
    public function toggle(Request $request, $statuteId)
    {
        $user = $request->user();
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('front.blog')->with('error', 'You must be logged in to react.');
        }

        $data = $request->validate([
            'reaction' => 'required|string|in:like,dislike',
        ]);

        $statute = Statute::findOrFail($statuteId);

        $existing = StatuteReaction::where('user_id', $user->id)
            ->where('statute_id', $statute->id)
            ->first();

        if ($existing) {
            if ($existing->reaction === $data['reaction']) {
                // same reaction -> remove (toggle off)
                $existing->delete();
            } else {
                // different reaction -> update
                $existing->reaction = $data['reaction'];
                $existing->save();
            }
        } else {
            StatuteReaction::create([
                'user_id' => $user->id,
                'statute_id' => $statute->id,
                'reaction' => $data['reaction'],
            ]);
        }

        $payload = [
            'likes' => $statute->likesCount(),
            'dislikes' => $statute->dislikesCount(),
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        // For web form submissions, redirect back with the updated counts (optional)
        return redirect()->back();
    }
}
