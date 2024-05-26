<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    // Show all listings
    public function index(){
        return view('listings.index', [
            'listings' => Listing::latest()->filter(request(['tag', 'search']))
                ->paginate(6)
        ]);
    }

    // Show single listing
    public function show(Listing $listing){
        return view('listings.show', [
            'listing' => $listing // this does the find method of eloquent model automatically
        ]);
    }

    // Show create form
    public function create(){
        return view('listings.create');
    }

    // Store in db
    public function store(Request $request){
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);
        
        if($request->hasFile('logo')){
            // Store in logos folder
            $formFields['logo'] = $request->file('logo')->store('logos', 'public'); 
        }

        $formFields['user_id'] = auth()->id(); 

        Listing::create($formFields);

        // One way of doing flash
        // Session::flash('message', "Listing Created");

        return redirect('/')->with('message', "Listing created succesfully.");
    }

    // Show edit form
    public function edit( Listing $listing) {
        return view('listings.edit', [
            'listing' => $listing
        ]);
    }

    // Update listing
    public function update(Request $request, Listing $listing){

        // Make sure logged in user is the owner
        if($listing->user_id != auth()->id()){
            abort(403, "Unauthorized Action.");
        }

        $formFields = $request->validate([
            'title' => 'required',
            'company' => 'required',
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);
        
        if($request->hasFile('logo')){
            // Store in logos folder
            $formFields['logo'] = $request->file('logo')->store('logos', 'public'); 
        }

        $listing->update($formFields);

        // One way of doing flash
        // Session::flash('message', "Listing Created");

        return back()->with('message', "Listing updated succesfully.");
    }

    // Delete listing
    public function destroy(Listing $listing) {

        // Make sure logged in user is the owner
        if($listing->user_id != auth()->id()){
            abort(403, "Unauthorized Action.");
        }

        $listing->delete();
        return redirect('/')->with('message', "Listing deleted succesfully.");
    }

    public function manage() {
        error_log('Ok');
        return view('listings.manage', ['listings' => auth()->user()->listings()->get()]);
    }
}
