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
            ->select(['co_art', 'art_des', 'stock_act', 'prec_vta1', 'co_lin', 'co_subl', 'co_cat'])
            ->with([
                'line:co_lin,lin_des'
            ])
            ->filter($filters)
            ->orderBy('art_des')
            ->paginate(15)
            ->withQueryString();

        // Obtener datos para los filtros
        $filterOptions = [
            'categories' => Category::select('co_cat', 'cat_des')->orderBy('cat_des')->get(),
            'lines' => Line::select('co_lin', 'lin_des')->orderBy('lin_des')->get(),
            'subls' => Subl::select('co_subl', 'subl_des')->orderBy('subl_des')->get(),
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
        try {
            $article = Article::with([
                'category:co_cat,cat_des',
                'line:co_lin,lin_des',
                'subl:co_subl,subl_des'
            ])
            ->where('co_art', $id)
            ->firstOrFail();

            return response()->json($article);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Artículo no encontrado',
                'message' => $e->getMessage()
            ], 404);
        }
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
