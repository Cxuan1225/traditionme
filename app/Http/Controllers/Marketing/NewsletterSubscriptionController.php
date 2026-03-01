<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing;

use App\Actions\Marketing\SubscribeNewsletterAction;
use App\DTOs\Marketing\SubscribeNewsletterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\SubscribeNewsletterRequest;
use Illuminate\Http\RedirectResponse;

class NewsletterSubscriptionController extends Controller
{
    public function store(
        SubscribeNewsletterRequest $request,
        SubscribeNewsletterAction $action,
    ): RedirectResponse {
        $action(SubscribeNewsletterData::fromRequest($request));

        return back()->with('status', 'You are subscribed to Tradition Me updates.');
    }
}
