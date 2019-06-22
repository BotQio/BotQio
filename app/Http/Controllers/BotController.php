<?php

namespace App\Http\Controllers;

use App\Bot;
use App\Cluster;
use App\Http\Requests\BotCreationRequest;
use App\Http\Requests\BotUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BotController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the list of available bots
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('bot.index', [
            'bots' => Auth::user()->bots()->with('cluster')->get()
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $clusters = Auth::user()->clusters;

        return view('bot.create', [
            'clusters' => $clusters
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BotCreationRequest $request)
    {
        /** @var Cluster $cluster */
        $cluster = Cluster::query()->find($request->get('cluster'));

        /** @var Bot $bot */
        $bot = new Bot([
            'name' => $request->get('name'),
            'type' => $request->get('type'),
            'creator_id' => Auth::id(),
            'cluster_id' => $cluster->id,
        ]);

        $bot->save();

        return redirect()->route('bots.show', [$bot]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Bot $bot
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Bot $bot)
    {
        $this->authorize('view', $bot);

        $bot->load(['cluster', 'creator']);

        return view('bot.show', [
            'bot' => $bot
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Bot $bot
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Bot $bot)
    {
        $this->authorize('update', $bot);

        return view('bot.edit', [
            'bot' => $bot
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BotUpdateRequest $request
     * @param \App\Bot $bot
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(BotUpdateRequest $request, Bot $bot)
    {
        $this->authorize('update', $bot);

        $bot->name = $request->get('name');
        $bot->save();

        return redirect()->route('bots.show', [$bot]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Bot  $bot
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bot $bot)
    {
        //
    }
}
