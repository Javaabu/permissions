import { LinkButton } from "@javaabu/forms";
import { RoleForm } from "../../components/RoleForm";
import type { RoleFormProps } from "../../types";

/** permissions::Roles/Create — a new role for the guard */
export default function Create(props: RoleFormProps) {
  const { strings, urls } = props;

  return (
    <div className="flex flex-col gap-6" data-roles>
      <div className="flex flex-wrap items-center justify-between gap-4">
        <h1 className="text-2xl font-semibold">{props.title}</h1>
        <LinkButton url={urls.index} color="secondary" size="sm" startIcon="chevron-left">
          {strings.back}
        </LinkButton>
      </div>

      <RoleForm role={null} action={urls.store} permissionGroups={props.permission_groups} translations={props.translations} strings={strings} />
    </div>
  );
}
