import { Card, LinkButton } from "@javaabu/forms";
import { RoleActions } from "../../components/RoleActions";
import type { RolesShowProps } from "../../types";

function CheckIcon() {
  return (
    <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className="text-success size-4 shrink-0">
      <path d="M20 6 9 17l-5-5" />
    </svg>
  );
}

/** permissions::Roles/Show — a role and the permissions it grants */
export default function Show(props: RolesShowProps) {
  const { strings, urls, role } = props;

  return (
    <div className="flex flex-col gap-6" data-roles>
      <div className="flex flex-wrap items-center justify-between gap-4">
        <h1 className="text-2xl font-semibold" dir="auto">
          {props.title}
        </h1>
        <LinkButton url={urls.index} color="secondary" size="sm" startIcon="chevron-left">
          {strings.back}
        </LinkButton>
      </div>

      <Card title={strings.details} footer={<RoleActions role={role} strings={strings} />}>
        <dl className="grid gap-4 sm:grid-cols-3">
          <div>
            <dt className="text-muted-foreground text-sm">{strings.name}</dt>
            <dd dir="ltr" className="text-start">
              {role.name}
            </dd>
          </div>
          <div>
            <dt className="text-muted-foreground text-sm">{strings.description}</dt>
            <dd dir="auto">{role.translated_description}</dd>
          </div>
          <div>
            <dt className="text-muted-foreground text-sm">{strings.guard}</dt>
            <dd dir="ltr" className="text-start">
              {props.guard}
            </dd>
          </div>
        </dl>
      </Card>

      <section className="flex flex-col gap-4" data-permission-groups>
        <h2 className="text-lg font-semibold">{strings.permissions}</h2>

        {props.permission_groups.length ? (
          props.permission_groups.map((group) => (
            <div key={group.name || "other"} data-permission-group={group.name}>
              <Card title={group.label}>
                <ul className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                  {group.permissions.map((permission) => (
                    <li key={permission.id} className="flex items-center gap-2" title={permission.name}>
                      <CheckIcon />
                      <span>{permission.description}</span>
                    </li>
                  ))}
                </ul>
              </Card>
            </div>
          ))
        ) : (
          <p className="text-muted-foreground">{strings.no_permissions}</p>
        )}
      </section>
    </div>
  );
}
