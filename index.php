<?php
function getTasks() {
    $tasks = [];
    if (file_exists("tasks.txt")) {
        $lines = file("tasks.txt", FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            $parts = explode("|", $line);
            $tasks[] = [
                'text' => $parts[0],
                'status' => $parts[1] ?? 'pending'
            ];
        }
    }
    return $tasks;
}

function saveTasks($tasks) {
    $file = fopen("tasks.txt", "w");
    foreach ($tasks as $task) {
        fwrite($file, $task['text'] . "|" . $task['status'] . PHP_EOL);
    }
    fclose($file);
}

function addTask($text) {
    $tasks = getTasks();
    $tasks[] = ['text' => $text, 'status' => 'pending'];
    saveTasks($tasks);
}

function deleteTask($index) {
    $tasks = getTasks();
    if (isset($tasks[$index])) {
        unset($tasks[$index]);
        saveTasks(array_values($tasks));
    }
}

function updateTask($index, $newText) {
    $tasks = getTasks();
    if (isset($tasks[$index])) {
        $tasks[$index]['text'] = $newText;
        saveTasks($tasks);
    }
}

function toggleComplete($index) {
    $tasks = getTasks();
    if (isset($tasks[$index])) {
        $tasks[$index]['status'] = $tasks[$index]['status'] === 'done' ? 'pending' : 'done';
        saveTasks($tasks);
    }
}

$message = "";

// Handle Add
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["task"])) {
    $task = trim($_POST["task"]);
    if (!empty($task)) {
        addTask($task);
        $message = "Task added!";
    } else {
        $message = "Please type something.";
    }
}

// Handle Edit
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_task"])) {
    $editIndex = (int) $_POST["index"];
    $newTask = trim($_POST["edit_task"]);
    if (!empty($newTask)) {
        updateTask($editIndex, $newTask);
        header("Location: index.php");
        exit;
    } else {
        $message = "Task can't be empty.";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    deleteTask((int)$_GET['delete']);
    header("Location: index.php");
    exit;
}

// Handle Mark Complete
if (isset($_GET['complete'])) {
    toggleComplete((int)$_GET['complete']);
    header("Location: index.php");
    exit;
}

$tasks = getTasks();
$editMode = isset($_GET['edit']);
$editIndex = $editMode ? (int)$_GET['edit'] : -1;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Task Manager</title>
</head>
<body>
    <h1>Task Manager</h1>

    <!-- Add Form -->
    <form method="post">
        <input type="text" name="task" placeholder="Enter a task">
        <button type="submit">Add Task</button>
    </form>

    <p><?php echo $message; ?></p>

    <h2>Your Tasks:</h2>
    <ul>
        <?php foreach ($tasks as $index => $task): ?>
            <li>
                <form method="get" style="display:inline;">
                    <input type="hidden" name="complete" value="<?php echo $index; ?>">
                    <input type="checkbox" onchange="this.form.submit()" <?php echo $task['status'] === 'done' ? 'checked' : ''; ?>>
                </form>

                <?php if ($editMode && $editIndex === $index): ?>
                    <form method="post" style="display:inline;">
                        <input type="text" name="edit_task" value="<?php echo htmlspecialchars($task['text']); ?>">
                        <input type="hidden" name="index" value="<?php echo $index; ?>">
                        <button type="submit">Save</button>
                    </form>
                <?php else: ?>
                    <span style="<?php echo $task['status'] === 'done' ? 'text-decoration:line-through;' : ''; ?>">
                        <?php echo htmlspecialchars($task['text']); ?>
                    </span>
                    <a href="?edit=<?php echo $index; ?>">(edit)</a>
                    <a href="?delete=<?php echo $index; ?>" style="color:red;">(delete)</a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
