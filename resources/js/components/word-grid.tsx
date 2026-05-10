import {
  Word,
} from '@/types'

export function WordGrid({ words }: { words: Word[] }) {
  return (
    <div>
      {words.map(word => (
        <div
          key={word.text}
          className={`flex items-center gap-4 rounded-lg px-3 py-1.5 hover:bg-accent${word.public_tts_url ? ' cursor-pointer select-none' : ''}`}
          onClick={() => word.public_tts_url && new Audio(word.public_tts_url).play()}
        >
          <span className="w-1/4 shrink-0 text-lg font-bold">{word.text}</span>
          <span className="w-1/4 shrink-0 text-sm text-muted-foreground">{word.pinyin}</span>
          <span className="min-w-0 flex-1 truncate text-right text-sm">{word.translation}</span>
        </div>
      ))}
    </div>
  )
}
