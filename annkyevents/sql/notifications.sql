CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    send_time DATETIME NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (send_time)
);