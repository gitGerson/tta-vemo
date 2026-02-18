<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{
    public function destroy($id)
    {
        $note = Note::find($id);

        // Delete from S3 if the file exists
        if ($note->image && Storage::disk('s3')->exists($note->image)) {
            Storage::disk('s3')->delete($note->image);
        }

        $note->delete();

        return redirect()->back()->with('success', 'Bukti perbaikan berhasil dihapus');
    }
}
