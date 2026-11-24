import { LinkButton } from "@javaabu/forms";
import { RoleActions } from "../../components/RoleActions";
import { RoleForm } from "../../components/RoleForm";
import type { RoleData, RoleFormProps } from "../../types";

/** permissions::Roles/Edit — a role's description and permissions */
export default function Edit(props: RoleFormProps) {
  const { strings, urls } = props;
  const role = props.role as RoleData;

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

      <RoleForm
        role={role}
        action={urls.update ?? role.links?.update ?? ""}
        permissionGroups={props.permission_groups}
        translations={props.translations}
        strings={strings}
      />

      {props.can.delete ? (
        <div className="flex justify-end">
          <RoleActions role={role} strings={strings} showEdit={false} />
        </div>
      ) : null}
    </div>
  );
}
