<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Article;
use App\Models\Category;
use App\Models\Line;
use App\Models\Subl;

class ArticleController extends Controller
{
    /**
     * Display a listing of articles.
     */
    public function index(Request $request)
    {
        // Obtener filtros de la request
        $filters = [
            'search' => $request->get('search', ''),
            'category' => $request->get('category', ''),
            'line' => $request->get('line', ''),
            'subl' => $request->get('subl', ''),
        ];

        // Aplicar filtros usando scopes
        $articles = Article::query()
            ->with(['category', 'line', 'subl'])
            ->filter($filters)
            ->orderBy('art_des')
            ->paginate(15)
            ->withQueryString();

        // Obtener datos para los filtros (solo una vez al cargar la página)
        $filterOptions = [
            'categories' => Category::orderBy('cat_des')->get(),
            'lines' => Line::orderBy('lin_des')->get(),
            'subls' => Subl::orderBy('subl_des')->get(),
        ];

        return Inertia::render('Articles/Index', [
            'articles' => $articles,
            'search' => $filters['search'],
            'categoryFilter' => $filters['category'],
            'lineFilter' => $filters['line'],
            'sublFilter' => $filters['subl'],
            'filterOptions' => $filterOptions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
