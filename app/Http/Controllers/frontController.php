<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\source;

class frontController extends Controller
{
    //source view loading

    public function sourceView()
    {
        $sources = source::all();
        return view('source', [
            'sources' => $sources,
        ]);
    }

    //save source
    public function saveSource(Request $request)
    {
        $data = new source();
        $data->source_name = $request->sourceName;
        if ($request->session()->has('business_id')) {
            $data->business_id = $request->session()->get('business_id');
        }

        if ($data->save()) :
            return back()->with('success', 'Success! Source added successfully');
        else :
            return back()->with('error', 'Opps! Source addition failed. Please try later');
        endif;
    }

    //source edit function
    public function sourceEdit($id)
    {
        return view('source', [
            'itemId' => $id,
        ]);
    }

    //source update function
    public function updateSource(Request $request)
    {
        $data = source::find($request->itemId);
        $data->source_name = $request->sourceName;  
        if ($request->session()->has('business_id')) {
            $data->business_id = $request->session()->get('business_id');
        }
        if ($data->save()) :
            return redirect(route('sourceView'))->with('success', 'Success! Source updated successfully');
        else :
            return back()->with('error', 'Opps! Source update failed. Please try later');
        endif;
    }

    //source delete function
    public function deleteSource($id)
    {
        $data = source::find($id);
        if ($data->delete()) :
            return back()->with('success', 'Success! Source deleted successfully');
        else :
            return back()->with('error', 'Opps! Source deletion failed. Please try later');
        endif;
    }

    // Bulk delete sources
    public function bulkDeleteSources(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer'
        ]);

        try {
            source::whereIn('id', $data['ids'])->delete();
            return redirect()->route('sourceView')->with('success','Selected sources deleted.');
        } catch (\Throwable $e) {
            return redirect()->route('sourceView')->with('error','Failed to delete selected sources.');
        }
    }

    //dashboard view loading
    public function dashboardView()
    {
        return view('dashboard');   
    }
    
}
