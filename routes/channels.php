<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('agents.{id}', function ($user, $id) {
    // Check if the authenticated user is the agent with this ID
    // Assuming the 'agent' relationship or logic exists to link User to Agent
    // For now, we check if the user's agent profile matches the ID
    return $user->agent && (int) $user->agent->id === (int) $id;
});
