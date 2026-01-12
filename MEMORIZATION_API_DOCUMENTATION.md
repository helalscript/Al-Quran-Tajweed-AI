# Memorization Feature API Documentation

## 📡 Response Format for Flutter

### Reverb Channel Events

#### Channel Name
```
memorization.user.{userId}
```

#### Event Name
```
memorization.ai-response
```

### Response Structure

Flutter app will receive real-time events with this structure:

```json
{
  "session_id": 1,
  "chunk": "AI response chunk text",
  "mistakes": [
    {
      "type": "wrong_word",
      "word_position": 2,
      "sentence_position": 1,
      "original_word": "بِسْمِ",
      "user_word": "بسم",
      "confidence": 0.95,
      "suggestion": "Add diacritical marks"
    }
  ],
  "is_complete": false,
  "mistakes_detail": [
    {
      "type": "wrong_word",
      "word_position": 2,
      "sentence_position": 1,
      "original_word": "بِسْمِ",
      "user_word": "بسم",
      "confidence": 0.95,
      "suggestion": "Add diacritical marks"
    }
  ]
}
```

### Mistake Types

- `missing_word` - Word is missing in user text
- `wrong_word` - Word is wrong/incomplete
- `extra_word` - Extra word added by user
- `pronunciation` - Pronunciation issue detected

### Flutter Implementation Guide

#### 1. Connect to Reverb Channel

```dart
// Example with Laravel Echo
final echo = Echo.private('memorization.user.${userId}');

echo.listen('.memorization.ai-response', (e) {
  final data = e.data;
  
  final sessionId = data['session_id'];
  final chunk = data['chunk'];
  final mistakes = data['mistakes_detail'] as List;
  final isComplete = data['is_complete'] as bool;
  
  // Process mistakes
  if (mistakes.isNotEmpty) {
    for (var mistake in mistakes) {
      final wordPosition = mistake['word_position'];
      final sentencePosition = mistake['sentence_position'];
      final originalWord = mistake['original_word'];
      final userWord = mistake['user_word'];
      final type = mistake['type'];
      
      // Highlight mistake in your UI
      highlightMistake(
        wordIndex: wordPosition,
        sentenceIndex: sentencePosition,
        type: type,
      );
    }
  }
});
```

#### 2. Word Position Mapping

- `word_position`: Index in the full original text (0-based)
- `sentence_position`: Index within the sentence (for UI highlighting)

#### 3. Display Mistakes in UI

```dart
Widget buildMistakeHighlight(String text, List<Map> mistakes) {
  // Split text into words
  final words = text.split(' ');
  
  return Wrap(
    children: words.asMap().entries.map((entry) {
      final index = entry.key;
      final word = entry.value;
      
      // Find mistake for this word position
      final mistake = mistakes.firstWhere(
        (m) => m['word_position'] == index,
        orElse: () => null,
      );
      
      if (mistake != null) {
        // Highlight mistake word
        return Container(
          padding: EdgeInsets.all(4),
          decoration: BoxDecoration(
            color: _getMistakeColor(mistake['type']),
            borderRadius: BorderRadius.circular(4),
          ),
          child: Text(
            word,
            style: TextStyle(
              color: Colors.white,
              decoration: TextDecoration.underline,
            ),
          ),
        );
      }
      
      return Text(word);
    }).toList(),
  );
}

Color _getMistakeColor(String type) {
  switch (type) {
    case 'missing_word':
      return Colors.red;
    case 'wrong_word':
      return Colors.orange;
    case 'extra_word':
      return Colors.purple;
    case 'pronunciation':
      return Colors.blue;
    default:
      return Colors.grey;
  }
}
```

---

## 🔌 API Endpoints

### 1. Start Session

**POST** `/api/memorization/sessions/start`

**Request:**
```json
{
  "surah_id": 1,
  "ayah_id": 1
}
```

**OR:**
```json
{
  "surah_name": "Al-Fatiha",
  "ayah_text": "1"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Memorization session started successfully",
  "data": {
    "session": {
      "id": 1,
      "user_id": 1,
      "surah_id": 1,
      "ayah_id": 1,
      "original_text": "بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ",
      "status": "in_progress",
      "started_at": "2026-01-07 14:00:00"
    },
    "channel_name": "memorization.user.1"
  }
}
```

### 2. Send Recitation (Voice Text)

**POST** `/api/memorization/sessions/{id}/recite`

**Request:**
```json
{
  "user_text": "بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Recitation processed successfully",
  "data": {
    "session": {
      "id": 1,
      "accuracy_score": 85.5,
      "total_mistakes": 2
    },
    "accuracy": 85.5
  }
}
```

**Note:** Real-time mistakes are sent via Reverb channel `memorization.user.{userId}`

### 3. Get Session

**GET** `/api/memorization/sessions/{id}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "original_text": "بِسْمِ اللَّهِ...",
    "user_recitation": "بسم الله...",
    "accuracy_score": 85.5,
    "total_mistakes": 2,
    "mistakes": [
      {
        "id": 1,
        "mistake_type": "wrong_word",
        "word_position": 0,
        "sentence_position": 0,
        "original_word": "بِسْمِ",
        "user_word": "بسم",
        "confidence_score": 95.0,
        "suggestion": "Add diacritical marks"
      }
    ]
  }
}
```

### 4. Get Mistakes

**GET** `/api/memorization/sessions/{id}/mistakes`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "mistake_type": "wrong_word",
      "word_position": 0,
      "sentence_position": 0,
      "original_word": "بِسْمِ",
      "user_word": "بسم",
      "confidence_score": 95.0
    }
  ]
}
```

### 5. Get History

**GET** `/api/memorization/history?per_page=25`

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "surah_id": 1,
        "ayah_id": 1,
        "accuracy_score": 85.5,
        "total_mistakes": 2,
        "created_at": "2026-01-07 14:00:00"
      }
    ]
  }
}
```

### 6. End Session

**PUT** `/api/memorization/sessions/{id}/end`

**Response:**
```json
{
  "success": true,
  "message": "Session ended successfully",
  "data": {
    "id": 1,
    "status": "completed",
    "completed_at": "2026-01-07 14:30:00"
  }
}
```

---

## 🎯 Key Points for Flutter

1. **Real-time Updates**: Listen to Reverb channel for live mistake detection
2. **Word Position**: Use `word_position` and `sentence_position` to highlight mistakes
3. **Mistake Types**: Different colors/styles for different mistake types
4. **Accuracy Score**: Display overall accuracy percentage
5. **Confidence**: Use `confidence` to show how sure AI is about the mistake

---

## ⚠️ Important Notes

1. **AI Integration Required**: Update `MemorizationService::streamAIResponse()` with your AI package
2. **Reverb Setup**: Make sure Laravel Reverb is installed and configured
3. **Authentication**: All endpoints require JWT authentication
4. **Channel Authorization**: Users can only access their own channel

