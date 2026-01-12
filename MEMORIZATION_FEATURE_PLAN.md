# Memorization Feature Plan - Live AI Streaming with Reverb

## 📋 Overview
এই feature-এর মাধ্যমে user-রা Quranic verses memorize করার সময় real-time AI feedback পাবে। Live streaming Reverb channel এর মাধ্যমে হবে।

---

## 🎯 Feature Requirements

### 1. **User Flow**
```
Frontend → API Request (surah_id + ayah_id + user_text from voice)
        → Fetch Original Text (from surah+ayah)
        → Send to Laravel AI (original_text vs user_text comparison)
        → AI Response Stream (mistakes detected)
        → Reverb Channel (live broadcast)
        → Frontend (receive & show mistakes in real-time)
```

**API Request Format:**
```json
{
  "surah_id": 1,
  "ayah_id": 1,
  // OR alternatively:
  "surah_name": "Al-Fatiha",
  "ayah_text": 1,
  "user_text": "بِسْمِ اللَّهِ..." // Voice converted text
}
```

### 2. **Database Structure**

#### Table: `memorization_sessions`
- `id` (primary key)
- `user_id` (foreign key → users)
- `surah_id` (integer, nullable) - Surah number/ID
- `surah_name` (string, nullable) - Surah name (alternative to surah_id)
- `ayah_id` (integer, nullable) - Single ayah ID (for single ayah memorization)
- `ayah_text` (string, nullable) - Ayah text in surah
- `ayah_start` (integer, nullable) - Starting ayah (for range)
- `ayah_end` (integer, nullable) - Ending ayah (for range)
- `original_text` (text) - Original Arabic text from Quran (fetched from surah+ayah)
- `user_recitation` (text) - User's recited text (voice converted)
- `status` (enum: 'in_progress', 'completed', 'paused', 'cancelled')
- `accuracy_score` (decimal, nullable) - AI calculated accuracy (0-100)
- `total_mistakes` (integer, default: 0)
- `ai_response` (text, nullable) - Full AI analysis response
- `started_at` (datetime)
- `completed_at` (datetime, nullable)
- `timestamps`

#### Table: `memorization_mistakes`
- `id` (primary key)
- `memorization_session_id` (foreign key)
- `mistake_type` (enum: 'missing_word', 'wrong_word', 'extra_word', 'pronunciation')
- `word_position` (integer) - Position in original text
- `original_word` (string) - What should be
- `user_word` (string, nullable) - What user said
- `confidence_score` (decimal, nullable) - AI confidence
- `corrected_at` (datetime, nullable) - When user corrected
- `timestamps`

---

## 🏗️ Technical Architecture

### 3. **Components Needed**

#### A. **Packages Required**
```bash
composer require laravel/reverb
composer require openai-php/laravel  # বা আপনার Laravel AI package
composer require guzzlehttp/guzzle   # HTTP client for streaming
```

#### B. **Files to Create**

1. **Migration Files**
   - `create_memorization_sessions_table.php`
   - `create_memorization_mistakes_table.php`

2. **Models**
   - `MemorizationSession.php`
   - `MemorizationMistake.php`

3. **Controller**
   - `API/V1/User/MemorizationController.php`
     - `startSession()` - Start new memorization session
     - `streamRecitation()` - Send Arabic text & receive AI stream
     - `getSession()` - Get session details
     - `endSession()` - End session
     - `getHistory()` - User's memorization history

4. **Service**
   - `Services/API/V1/User/MemorizationService.php`
     - `createSession()` - Create new session
     - `fetchOriginalText()` - Fetch original text from surah+ayah (Al-Quran API)
     - `processRecitation()` - Send original_text vs user_text to AI
     - `streamAIResponse()` - Stream AI comparison response via Reverb
     - `analyzeMistakes()` - Parse AI response & save mistakes to DB
     - `calculateAccuracy()` - Calculate accuracy score
     - `updateSession()` - Update session status

5. **Events & Listeners**
   - `Events/MemorizationAIResponse.php` - Event when AI responds
   - `Listeners/BroadcastAIResponse.php` - Broadcast to Reverb channel

6. **Broadcasting**
   - `routes/channels.php` - Channel authorization
   - Channel name: `memorization.user.{userId}`

---

## 🔄 Live Streaming Flow

### 4. **Step-by-Step Process**

```
1. User starts session
   POST /api/memorization/sessions/start
   → Creates session in DB
   → Returns session_id & channel_name

2. User connects to Reverb channel
   Frontend connects to: memorization.user.{userId}

3. User sends recitation data
   POST /api/memorization/sessions/{id}/recite
   Body: { 
     "surah_id": 1,
     "ayah_id": 1,
     "user_text": "بِسْمِ اللَّهِ..." // Voice converted text
   }

4. Backend Processing:
   a. Fetch original_text from Al-Quran API/DB using surah_id + ayah_id
   b. Save user_recitation to DB
   c. Send to Laravel AI for comparison:
      - Prompt: "Compare original text vs user text"
      - Original: {original_text}
      - User: {user_text}
      - Task: Detect mistakes, missing words, wrong words
   d. AI processes and streams response chunks (with streaming)
   e. Each chunk → Broadcast to Reverb channel
   f. Frontend receives chunks in real-time
   g. Analyze mistakes & save to mistakes table
   h. Calculate accuracy score

5. User receives feedback
   - Mistakes highlighted in real-time
   - Accuracy score shown
   - Suggestions displayed
```

---

## 📡 Reverb Channel Structure

### 5. **Channel Configuration**

```php
// routes/channels.php
Broadcast::channel('memorization.user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

**Channel Events:**
- `memorization.ai-response` - AI response chunk
- `memorization.mistake-detected` - Real-time mistake
- `memorization.session-updated` - Session status change
- `memorization.completed` - Session completion

---

## 🤖 AI Integration (Prism Package)

### 6. **Streaming Implementation**

```php
// In MemorizationService
public function streamAIResponse($sessionId, $originalText, $userText) {
    // Prepare AI prompt for comparison
    $prompt = "Compare these two Arabic texts and identify mistakes:\n\n";
    $prompt .= "ORIGINAL TEXT (from Quran):\n{$originalText}\n\n";
    $prompt .= "USER RECITED TEXT (voice converted):\n{$userText}\n\n";
    $prompt .= "Please analyze and identify:\n";
    $prompt .= "1. Missing words\n";
    $prompt .= "2. Wrong words\n";
    $prompt .= "3. Extra words\n";
    $prompt .= "4. Pronunciation issues\n";
    $prompt .= "Format response as JSON with mistakes array.";
    
    // Send to Laravel AI with streaming
    $stream = AI::chat()
        ->model('your-model') // or Prism package
        ->stream()
        ->send($prompt);
    
    $fullResponse = '';
    
    // Process stream chunks
    foreach ($stream as $chunk) {
        $response = $chunk->content;
        $fullResponse .= $response;
        
        // Broadcast to Reverb in real-time
        broadcast(new MemorizationAIResponse(
            sessionId: $sessionId,
            chunk: $response,
            userId: auth()->id()
        ))->toOthers();
    }
    
    // After streaming complete, parse and save mistakes
    $this->analyzeMistakes($sessionId, $fullResponse);
}
```

---

## 📊 API Endpoints

### 7. **API Routes**

```php
Route::group(['middleware' => ['auth:api', 'is_user']], function () {
    // Sessions
    Route::post('memorization/sessions/start', [MemorizationController::class, 'startSession']);
    Route::get('memorization/sessions/{id}', [MemorizationController::class, 'getSession']);
    Route::put('memorization/sessions/{id}/end', [MemorizationController::class, 'endSession']);
    
    // Recitation (with surah_id + ayah_id + user_text from voice)
    Route::post('memorization/sessions/{id}/recite', [MemorizationController::class, 'streamRecitation']);
    
    // History
    Route::get('memorization/history', [MemorizationController::class, 'getHistory']);
    Route::get('memorization/sessions/{id}/mistakes', [MemorizationController::class, 'getMistakes']);
});
```

**Request Body Example:**
```json
{
  "surah_id": 1,
  "ayah_id": 1,
  "user_text": "بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ"
}
```

**OR using surah_name:**
```json
{
  "surah_name": "Al-Fatiha",
  "ayah_number": 1,
  "user_text": "بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ"
}
```

---

## 🗄️ Database Schema Details

### 8. **Table Fields with Casts**

**memorization_sessions:**
- `surah_id` → integer
- `ayah_id` → integer
- `status` → enum cast
- `accuracy_score` → decimal:2
- `total_mistakes` → integer
- `started_at` → datetime
- `completed_at` → datetime

**memorization_mistakes:**
- `mistake_type` → enum cast
- `confidence_score` → decimal:2
- `corrected_at` → datetime

---

## 🔐 Security & Performance

### 9. **Important Considerations**

1. **Authentication**: All endpoints require `auth:api`
2. **Channel Authorization**: Users can only access their own channel
3. **Rate Limiting**: Limit API calls per user
4. **Queue Jobs**: Heavy AI processing should use queues
5. **Error Handling**: Proper error responses for AI failures
6. **Streaming Timeout**: Set timeout for long AI responses

---

## 📝 Next Steps After Approval

1. ✅ Create migrations
2. ✅ Create models with casts
3. ✅ Create controller
4. ✅ Create service
5. ✅ Setup Reverb channels
6. ✅ Create events/listeners
7. ✅ Integrate Laravel AI (Prism)
8. ✅ Add routes
9. ✅ Test streaming flow

---

## 📝 Updated Requirements Summary

### **Frontend → Backend Communication:**
1. Frontend পাঠাবে:
   - `surah_id` + `ayah_id` (primary method)
   - অথবা `surah_name` + `ayah_number` (alternative)
   - `user_text` (voice থেকে converted Arabic text)

2. Backend করবে:
   - surah+ayah থেকে original text fetch করবে (Al-Quran API ব্যবহার করে)
   - original_text vs user_text AI-তে পাঠাবে comparison এর জন্য
   - AI mistakes detect করে live stream করবে Reverb channel এ
   - Mistakes save করবে database এ
   - Accuracy score calculate করবে

### **AI Comparison Task:**
- Original Quran text (surah+ayah থেকে fetched)
- User recited text (voice converted)
- AI compare করে mistakes identify করবে:
  - Missing words
  - Wrong words  
  - Extra words
  - Pronunciation issues

---

## ❓ Questions to Confirm

1. Which Laravel AI package are you using? (Prism/OpenAI/Anthropic?)
2. Al-Quran API কোনটা use করবেন? (existing AlQuranController এ যা আছে?)
3. Voice to text conversion frontend এ হবে না backend এ?

---

**Ready to implement?** Review করে confirm করুন, তারপর আমি সব files create করে দিব! 🚀

