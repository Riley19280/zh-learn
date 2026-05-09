export interface Character {
  character: string
}

export interface Word {
  id: number
  text: string
  pinyin: string
  translation: string | null
  ttsUrl: string | null
  isAvailable?: boolean
}

export interface Section {
  id: number
  title: string
  sectionNumber: number
  unitNumber: number
  wordsCount?: number
  isUnlocked?: boolean
  words?: Word[]
}

export interface PracticeSet {
  id: number
  name: string

  words?: Word[]
  words_count?: number
}

export interface PracticeSession {
  id: number
  exercise_structure: string
  exercise_type: string
  question_form: string
  answer_form: string
  attempts_count: number
  correct_attempts_count: number
  completed_at: string

  practice_set?: PracticeSet
}

export interface PracticeAttempt {
  id: number
  word_id: number
  practice_session_id: number
  is_correct: boolean
  given_answer: string | null
  correct_answer: string
  response_time_ms: number | null
  options: string[] | null
  feedback: string | null

  word?: Word
}

export type UnsavedPracticeAttempt = Omit<PracticeAttempt, 'id' | 'practice_session_id'>
