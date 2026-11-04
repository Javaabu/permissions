import { LinkButton, Pagination, SearchForm, Table, TableCell, TableEmptyRow, TableHeading, TableRow } from "@javaabu/forms";
import { RoleActions } from "../../components/RoleActions";
import type { RolesIndexProps } from "../../types";

/** permissions::Roles/Index — the guard's roles, with search */
export default function Index(props: RolesIndexProps) {
  const { strings, urls, roles } = props;

  return (
    <div className="flex flex-col gap-6" data-roles>
      <div className="flex flex-wrap items-center justify-between gap-4">
        <h1 className="text-2xl font-semibold">{props.title}</h1>
        {props.can.create ? (
          <LinkButton url={urls.create} startIcon="plus">
            {strings.new_role}
          </LinkButton>
        ) : null}
      </div>

      <SearchForm action={urls.index} placeholder={strings.search} />

      <Table
        model="roles"
        noCheckbox
        noBulk
        headers={
          <>
            <TableHeading label={strings.name} />
            <TableHeading label={strings.description} />
            <TableHeading label={strings.permissions} />
            <TableHeading label={strings.actions} />
          </>
        }
        pagination={roles.meta.last_page > 1 ? <Pagination paginator={roles} /> : null}
      >
        {roles.data.length ? (
          roles.data.map((role) => (
            <TableRow key={role.id} modelId={String(role.id)} noCheckbox data-role-row={role.id}>
              <TableCell name="name" label={strings.name}>
                {role.can.view && role.links ? (
                  <a href={role.links.show} className="font-medium hover:underline" dir="ltr">
                    {role.name}
                  </a>
                ) : (
                  <span className="font-medium" dir="ltr">
                    {role.name}
                  </span>
                )}
              </TableCell>
              <TableCell name="description" label={strings.description}>
                <span dir="auto">{role.translated_description}</span>
              </TableCell>
              <TableCell name="permissions_count" label={strings.permissions}>
                <span className="tabular-nums">{strings.permissions_count.replace(":count", String(role.permissions_count ?? 0))}</span>
              </TableCell>
              <TableCell name="actions" label={strings.actions}>
                <RoleActions role={role} strings={strings} />
              </TableCell>
            </TableRow>
          ))
        ) : (
          <TableEmptyRow columns={4} noCheckbox>
            {props.search ? (
              <p className="font-medium">{strings.no_results}</p>
            ) : (
              <>
                <p className="font-medium">{strings.no_roles}</p>
                <p className="text-muted-foreground text-sm">{strings.no_roles_message}</p>
              </>
            )}
          </TableEmptyRow>
        )}
      </Table>
    </div>
  );
}
