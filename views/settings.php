<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Piano Scale Reps</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/app.css">
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">Piano Scale Reps</a>
            <div class="d-flex align-items-center">
                <a href="/" class="btn btn-outline-light me-2">Back to Practice</a>
                <?php if ($isLoggedIn): ?>
                    <span class="text-light me-2 d-none d-md-inline"><?= htmlspecialchars($user['email']) ?></span>
                    <a href="/logout" class="btn btn-outline-light btn-sm">Logout</a>
                <?php else: ?>
                    <a href="/login" class="btn btn-outline-light btn-sm">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mb-4">Settings</h2>
                
                <!-- Configuration Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Practice Configuration</h5>
                    </div>
                    <div class="card-body">
                        <form hx-post="/settings" hx-target="#settings-message">
                            
                            <div class="mb-3">
                                <label for="required_successes" class="form-label">
                                    Required Successes (X)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="required_successes" 
                                       name="required_successes" 
                                       min="1" 
                                       max="10" 
                                       value="<?= $config['required_successes'] ?? 2 ?>"
                                       required>
                                <div class="form-text">
                                    Number of first-try successes needed per scale (1-10)
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="allow_repeat" 
                                           name="allow_repeat"
                                           <?= ($config['allow_repeat_when_last_only'] ?? '1') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="allow_repeat">
                                        Allow immediate repeat when only one scale remains
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="show_notes" 
                                           name="show_notes"
                                           <?= ($config['show_notes'] ?? '1') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="show_notes">
                                        Show scale notes on practice cards
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </form>
                        
                        <div id="settings-message" class="mt-3"></div>
                    </div>
                </div>
                
                <!-- Scales Management -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Manage Scales</h5>
                        <?php if ($isLoggedIn): ?>
                            <span class="badge bg-success">Signed in as <?= htmlspecialchars($user['email']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if ($canManageScales): ?>
                        <!-- Add New Scale -->
                        <form hx-post="/scale/add" hx-target="body" hx-swap="outerHTML scroll:no-scroll" class="mb-4">

                            <div class="row g-2">
                                <div class="col-md-4">
                                    <input type="text"
                                           class="form-control"
                                           name="name"
                                           placeholder="Scale name"
                                           required>
                                </div>
                                <div class="col-md-3">
                                    <input type="text"
                                           class="form-control"
                                           name="notes"
                                           placeholder="Notes (optional)">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="type">
                                        <?php foreach ($scaleTypes as $type): ?>
                                            <option value="<?= htmlspecialchars($type->name) ?>" <?= $lastScaleType === $type->name ? 'selected' : '' ?>><?= htmlspecialchars($type->name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success w-100">Add</button>
                                </div>
                            </div>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-info mb-4">
                            <a href="/login">Sign in</a> to add or remove scales.
                        </div>
                        <?php endif; ?>

                        <!-- Existing Scales -->
                        <div id="settings-content">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Scale Name</th>
                                            <th>Type</th>
                                            <?php if ($canManageScales): ?>
                                            <th width="100">Action</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($scales as $scale): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($scale->name) ?></td>
                                                <td><span class="badge bg-secondary"><?= htmlspecialchars($scale->type ?? 'Other') ?></span></td>
                                                <?php if ($canManageScales): ?>
                                                <td>
                                                    <button class="btn btn-sm btn-danger"
                                                            hx-post="/scale/delete/<?= $scale->id ?>"
                                                            hx-target="body"
                                                            hx-swap="outerHTML scroll:no-scroll"
                                                            hx-confirm="Delete <?= htmlspecialchars($scale->name) ?>?">
                                                        Delete
                                                    </button>
                                                </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scale Types Management -->
                <?php if ($canManageScales): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Manage Scale Types</h5>
                    </div>
                    <div class="card-body">
                        <!-- Add New Type -->
                        <form hx-post="/scale-type/add" hx-target="body" hx-swap="outerHTML scroll:no-scroll" class="mb-4">
                            <div class="row g-2">
                                <div class="col-md-8">
                                    <input type="text"
                                           class="form-control"
                                           name="name"
                                           placeholder="New type name"
                                           required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-success w-100">Add Type</button>
                                </div>
                            </div>
                        </form>

                        <!-- Existing Types -->
                        <div id="types-content">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Type Name</th>
                                            <th width="100">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($scaleTypes as $type): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($type->name) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                            hx-post="/scale-type/delete/<?= $type->id ?>"
                                                            hx-target="body"
                                                            hx-swap="outerHTML scroll:no-scroll"
                                                            hx-confirm="Delete type '<?= htmlspecialchars($type->name) ?>'? (Only works if no scales use this type)">
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Version footer -->
        <div class="text-center text-muted mt-5 mb-3">
            <small>Version 1.3.0</small>
        </div>
    </div>
    
    <!-- CSRF for HTMX -->
    <meta name="csrf-name" content="<?= htmlspecialchars($csrfNameValue) ?>">
    <meta name="csrf-value" content="<?= htmlspecialchars($csrfTokenValue) ?>">
    <script>
        document.body.addEventListener('htmx:configRequest', (event) => {
            const csrfName = document.querySelector('meta[name="csrf-name"]').content;
            const csrfValue = document.querySelector('meta[name="csrf-value"]').content;
            event.detail.parameters['csrf_name'] = csrfName;
            event.detail.parameters['csrf_value'] = csrfValue;
        });

        // Preserve scroll position across page refreshes
        let savedScrollY = 0;
        document.body.addEventListener('htmx:beforeSwap', () => {
            savedScrollY = window.scrollY;
        });
        document.body.addEventListener('htmx:afterSwap', () => {
            window.scrollTo(0, savedScrollY);
        });
    </script>
</body>
</html>