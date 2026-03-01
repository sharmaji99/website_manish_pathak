<?php

declare(strict_types=1);

/*
 * Backward-compatible endpoint used by the existing frontend fetch call.
 * Keeps frontend IDs/classes/validation untouched while routing to the new handler.
 */
require __DIR__ . '/submit-consultation.php';
