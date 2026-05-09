import {
  Form,
  Head,
  usePage,
} from '@inertiajs/react'
import InputError from '@/components/input-error'
import PasswordInput from '@/components/password-input'
import TextLink from '@/components/text-link'
import {
  Button,
} from '@/components/ui/button'
import {
  Input,
} from '@/components/ui/input'
import {
  Label,
} from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  Spinner,
} from '@/components/ui/spinner'
import {
  login,
} from '@/routes'
import {
  store,
} from '@/routes/register'
import {
  Section,
} from '@/types'

interface Props {
  sections: Section[]
  [key: string]: unknown
}

export default function Register() {
  const { sections } = usePage<Props>().props

  return (
    <>
      <Head title="Register" />
      <Form
        {...store.form()}
        resetOnSuccess={['password', 'password_confirmation']}
        disableWhileProcessing
        className="flex flex-col gap-6"
      >
        {({ processing, errors }) => (
          <>
            <div className="grid gap-6">
              <div className="grid gap-2">
                <Label htmlFor="name">Name</Label>
                <Input
                  id="name"
                  type="text"
                  required
                  autoFocus
                  tabIndex={1}
                  autoComplete="name"
                  name="name"
                  placeholder="Full name"
                />
                <InputError
                  message={errors.name}
                  className="mt-2"
                />
              </div>

              <div className="grid gap-2">
                <Label htmlFor="email">Email address</Label>
                <Input
                  id="email"
                  type="email"
                  required
                  tabIndex={2}
                  autoComplete="email"
                  name="email"
                  placeholder="email@example.com"
                />
                <InputError message={errors.email} />
              </div>

              <div className="grid gap-2">
                <Label htmlFor="section_id">Current section</Label>
                <Select name="section_id" required>
                  <SelectTrigger id="section_id" tabIndex={3} className="w-full">
                    <SelectValue placeholder="Select your unit and section" />
                  </SelectTrigger>
                  <SelectContent>
                    {sections.map(s => (
                      <SelectItem key={s.id} value={String(s.id)}>
                        {s.title}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <InputError message={errors.section_id} />
              </div>

              <div className="grid gap-2">
                <Label htmlFor="password">Password</Label>
                <PasswordInput
                  id="password"
                  required
                  tabIndex={4}
                  autoComplete="new-password"
                  name="password"
                  placeholder="Password"
                />
                <InputError message={errors.password} />
              </div>

              <div className="grid gap-2">
                <Label htmlFor="password_confirmation">
                  Confirm password
                </Label>
                <PasswordInput
                  id="password_confirmation"
                  required
                  tabIndex={5}
                  autoComplete="new-password"
                  name="password_confirmation"
                  placeholder="Confirm password"
                />
                <InputError
                  message={errors.password_confirmation}
                />
              </div>

              <Button
                type="submit"
                className="mt-2 w-full"
                tabIndex={6}
                data-test="register-user-button"
              >
                {processing && <Spinner />}
                Create account
              </Button>
            </div>

            <div className="text-center text-sm text-muted-foreground">
              Already have an account?
              {' '}
              <TextLink href={login()} tabIndex={7}>
                Log in
              </TextLink>
            </div>
          </>
        )}
      </Form>
    </>
  )
}

Register.layout = {
  title: 'Create an account',
  description: 'Enter your details below to create your account',
}
