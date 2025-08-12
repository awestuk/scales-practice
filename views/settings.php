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
            <a class="navbar-brand" href="/">🎹 Piano Scale Reps</a>
            <a href="/" class="btn btn-outline-light">Back to Practice</a>
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
                                       value="<?= $config['required_successes'] ?? 3 ?>"
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
                            
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </form>
                        
                        <div id="settings-message" class="mt-3"></div>
                    </div>
                </div>
                
                <!-- Scales Management -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Manage Scales</h5>
                    </div>
                    <div class="card-body">
                        <!-- Add New Scale -->
                        <form hx-post="/scale/add" hx-target="#settings-content" class="mb-4">
                            
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input type="text" 
                                           class="form-control" 
                                           name="name" 
                                           placeholder="Scale name" 
                                           required>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" 
                                           class="form-control" 
                                           name="notes" 
                                           placeholder="Notes (optional)">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success w-100">Add</button>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Existing Scales -->
                        <div id="settings-content">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Scale Name</th>
                                            <th>Notes</th>
                                            <th width="100">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($scales as $scale): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($scale->name) ?></td>
                                                <td><?= htmlspecialchars($scale->notes ?? '') ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-danger"
                                                            hx-post="/scale/delete/<?= $scale->id ?>"
                                                            hx-target="#settings-content"
                                                            hx-confirm="Delete <?= htmlspecialchars($scale->name) ?>?">
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
            </div>
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
    </script>
</body>
</html>