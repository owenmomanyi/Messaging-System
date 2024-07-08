<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch contacts for sidebar
$sql_contacts = "SELECT * FROM contacts WHERE user_id = ?";
$stmt_contacts = $conn->prepare($sql_contacts);
$stmt_contacts->bind_param("i", $user_id);
$stmt_contacts->execute();
$result_contacts = $stmt_contacts->get_result();

if (!$result_contacts) {
    echo "Error: " . $conn->error;
}

// Display selected contact's messages
$contact_id = isset($_GET['contact_id']) ? $_GET['contact_id'] : null;

if ($contact_id) {
    // Fetch contact details
    $sql_contact = "SELECT * FROM contacts WHERE id = ? AND user_id = ?";
    $stmt_contact = $conn->prepare($sql_contact);
    $stmt_contact->bind_param("ii", $contact_id, $user_id);
    $stmt_contact->execute();
    $result_contact = $stmt_contact->get_result();

    if ($result_contact && $result_contact->num_rows > 0) {
        $selected_contact = $result_contact->fetch_assoc();

        // Fetch messages between user and selected contact
        $sql_messages = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY sent_at ASC";
        $stmt_messages = $conn->prepare($sql_messages);
        $stmt_messages->bind_param("iiii", $user_id, $contact_id, $contact_id, $user_id);
        $stmt_messages->execute();
        $result_messages = $stmt_messages->get_result();

        if (!$result_messages) {
            echo "Error fetching messages: " . $conn->error;
        }
    } else {
        echo "Invalid contact ID or unauthorized access.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Messaging App</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="home.html" class="active">Home      </a>
            <a href="services.html">Services    </a>
            <a href="about.html">About   </a>
            <a href="index.php">Messaging   </a>
        </div>
    </nav>
    <div class="content-container">
        <div class="messaging-app">
            <div class="sidebar">
                <br>
                <br>
                <br>
                <h2>Contacts</h2>
                <ul>
                    <?php while ($contact = $result_contacts->fetch_assoc()) { ?>
                        <li><a href="index.php?contact_id=<?php echo $contact['id']; ?>"><?php echo htmlspecialchars($contact['contact_name']); ?></a></li>
                    <?php } ?>
                </ul>
                <form method="POST" action="add_contact.php">
                    <input type="text" name="contact_name" placeholder="Contact Name" required>
                    <input type="text" name="contact_phone" placeholder="Contact Phone" required>
                    <button type="submit">Add Contact</button>
                </form>
            </div>
            
            <div class="chat">
                <br>
                <br>
                <br>
                <div class="chat-header">
                    <?php if (isset($selected_contact)) : ?>
                        <h3><?php echo htmlspecialchars($selected_contact['contact_name']); ?></h3>
                        <p><?php echo htmlspecialchars($selected_contact['contact_phone']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="messages <?php echo isset($contact_id) ? 'active' : ''; ?>">
                    <?php if (isset($result_messages)) {
                        while ($message = $result_messages->fetch_assoc()) {
                            $message_class = $message['sender_id'] == $user_id ? 'sent' : 'received';
                    ?>
                            <div class="message <?php echo $message_class; ?>">
                                <?php echo htmlspecialchars($message['message']); ?>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
                
                <?php if (isset($contact_id)) : ?>
                    <form id="sendMessageForm" method="POST" action="send_sms.php">
                        <textarea id="messageInput" name="message" placeholder="Type your message" required></textarea>
                        <input type="hidden" name="receiver_id" value="<?php echo $contact_id; ?>">
                        <button type="submit" onclick="sendMessage(event)">Send</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function sendMessage(event) {
            var form = document.getElementById('sendMessageForm');
            var messageInput = document.getElementById('messageInput');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', form.action, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        // Message sent successfully
                        var messageHtml = xhr.responseText;
                        var messagesDiv = document.querySelector('.messages');
                        messagesDiv.innerHTML += messageHtml;

                        // Clear the message input
                        messageInput.value = '';
                    } else {
                        // Error sending message
                        console.error('Error sending message:', xhr.responseText);
                        alert('Error sending message: ' + xhr.responseText);
                    }
                }
            };

            var formData = new FormData(form);
            var params = new URLSearchParams(formData).toString();

            xhr.send(params);

            event.preventDefault();
        }
    </script>
</body>
</html>
