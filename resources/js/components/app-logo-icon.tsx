import type {
  SVGAttributes,
} from 'react'

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
  return (
      <svg width="512" height="512" viewBox="0 0 512 512" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
              <linearGradient id="grad" x1="80" y1="80" x2="432" y2="432" gradientUnits="userSpaceOnUse">
                  <stop stop-color="#38BDF8"/>
                  <stop offset="1" stop-color="#8B5CF6"/>
              </linearGradient>
          </defs>

          <g
              stroke="url(#grad)"
              stroke-width="30"
              stroke-linecap="round"
              stroke-linejoin="round"
          >
              <path d="M96 140H240L120 372H264" />

              <path d="M264 372H312" />

              <path d="M312 140V372" />
              <path d="M416 140V372" />
              <path d="M312 256H416" />
          </g>

          <g fill="#7C4DFF">
              <circle cx="96" cy="140" r="10"/>
              <circle cx="240" cy="140" r="10"/>
              <circle cx="120" cy="372" r="10"/>
              <circle cx="264" cy="372" r="10"/>

              <circle cx="312" cy="372" r="10"/>

              <circle cx="312" cy="140" r="10"/>
              <circle cx="416" cy="140" r="10"/>
              <circle cx="312" cy="256" r="10"/>
              <circle cx="416" cy="256" r="10"/>
              <circle cx="416" cy="372" r="10"/>
          </g>
      </svg>
  )
}
