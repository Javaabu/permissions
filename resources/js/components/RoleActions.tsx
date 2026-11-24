import { Form, LinkButton, Submit } from "@javaabu/forms";
import type { MouseEvent } from "react";
import type { RoleData, RolesStrings } from "../types";

export interface RoleActionsProps {
  role: RoleData;
  strings: Pick<RolesStrings, "view" | "edit" | "delete" | "confirm_delete">;
  /** Show the View button. Default `false` (the name links to the role) */
  showView?: boolean;
  /** Show the Edit button. Default `true` */
  showEdit?: boolean;
}

function confirmFirst(message: string) {
  return (event: MouseEvent<HTMLButtonElement>) => {
    if (!window.confirm(message)) {
      event.preventDefault();
    }
  };
}

/** View, edit and delete buttons, shown when the user may use them */
export function RoleActions({ role, strings, showView = false, showEdit = true }: RoleActionsProps) {
  const links = role.links;

  if (!links) {
    return null;
  }

  return (
    <div className="flex flex-wrap items-center gap-2" data-role-actions>
      {showView && role.can.view ? (
        <LinkButton url={links.show} color="secondary" size="sm">
          {strings.view}
        </LinkButton>
      ) : null}

      {showEdit && role.can.update ? (
        <LinkButton url={links.edit} color="secondary" size="sm">
          {strings.edit}
        </LinkButton>
      ) : null}

      {role.can.delete ? (
        <Form method="DELETE" action={links.destroy}>
          <Submit color="danger" size="sm" animate={false} onClick={confirmFirst(strings.confirm_delete)}>
            {strings.delete}
          </Submit>
        </Form>
      ) : null}
    </div>
  );
}
