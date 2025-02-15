<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# ChatBot Session and Token Explanation

## Overview

This project involves creating a chatbot system that allows both authenticated and guest users to chat with the bot. The bot's responses are stored, and for authenticated users, previous chat history is maintained. The system relies on tokens for user authentication and sessions to track individual chat interactions.

## Key Concepts

### 1. **Session**
A session is a unique identifier that tracks the user's conversation with the bot. It allows us to store the entire chat history of a user under a specific session ID.
- Each user is assigned a session ID, which is generated when the user makes a request.
- For authenticated users, the session ID remains the same as long as the token is valid.
- A session is tied to the user, allowing the chatbot to retrieve and display previous messages.

In the example provided, you can see that the `session_id` is used to group messages together.
For example:

| user_id | session_id | user_message           | bot_response            | created_at           |
|---------|------------|------------------------|-------------------------|----------------------|
| 1       | 2          | whats 2 divided by 0    | 0 is not defined as a number that can be divided,...     | 2025-02-15 15:04:07  |
| 1       | 2          | again??                | 	2 divided by 0 is undefined in standard mathem...    | 2025-02-15 15:04:37  |

Both messages are under the same session (`session_id` = 2), showing that they are part of the same ongoing conversation.

### 2. **Token**
A token is used to authenticate the user. It is a string of characters sent by the user in the request header to validate their identity.
- When a user logs in, they receive an authentication token.
- This token is sent with every request to authenticate and verify the user.
- If the token is invalid or expired, the user is asked to log in again to get a new token.

### How Tokens and Sessions Work Together

- When the user sends a request with a valid token, the system checks the token and retrieves the associated user.
- If the token is valid, the system will use the same session for the user unless the token expires.
- For each request, a new `session_id` is created for the user, and the system uses that session ID to store and retrieve the user's chat history.

If the token is changed or expired, a new session will be generated. However, as long as the token is valid and unchanged, the session will remain the same. This allows the chatbot to maintain a continuous conversation history for the user.

### Example Workflow

1. **Authenticated User**:
    - A user sends a message to the chatbot with their authentication token.
    - The system validates the token and checks if the session already exists for that user.
    - If the session exists, the system retrieves the user's previous messages and appends the new message to the conversation.
    - The bot responds, and the entire conversation (with new messages) is stored under the same session.

2. **Guest User**:
    - If the user is not authenticated (no token provided), the system treats the user as a guest.
    - A new session is generated for the guest.
    - Only the current message is stored, and no previous chat history is maintained for guests.

---

## Session and Token Flow Diagram

*Refer to the database table image for better understanding of how chat history is stored based on session IDs.*

- **Token Validation**:
    - The token is sent via the `Authorization` header in the request. The server validates it to ensure the request comes from an authenticated user.

- **Session Handling**:
    - The session is either created or retrieved using the token and is used to group messages together in the chat history.

---

## Conclusion

In summary, the session ID and token work together to:
- Authenticate users.
- Maintain continuous chat histories for each user session.
- Ensure that the chatbot responds with context from previous messages (for authenticated users) while keeping track of new conversations.

This system makes it possible to handle both authenticated and guest users while providing a consistent experience across sessions.
