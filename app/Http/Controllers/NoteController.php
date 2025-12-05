<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function destroy($id)
    {
        $note = Note::find($id);
        $imagePath = public_path('notes/' . $note->image);

        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        $note->delete();

        return redirect()->back()->with('success', 'Bukti perbaikan berhasil dihapus');
    }
}
