/**
 * For the Blade role pages.
 *
 * Buttons marked data-permission-group-select="all" or "none" tick or untick every permission
 * in their group (data-permission-group). Forms with data-confirm ask before they submit.
 */

export function selectGroup(group: Element, checked: boolean): void {
  group.querySelectorAll<HTMLInputElement>('input[type="checkbox"][name="permissions[]"]').forEach((checkbox) => {
    if (checkbox.disabled || checkbox.checked === checked) {
      return;
    }

    checkbox.checked = checked;
    checkbox.dispatchEvent(new Event("change", { bubbles: true }));
  });
}

function onClick(event: Event): void {
  const button = (event.target as Element | null)?.closest?.<HTMLElement>("[data-permission-group-select]");

  if (!button) {
    return;
  }

  const group = button.closest("[data-permission-group]");

  if (!group) {
    return;
  }

  event.preventDefault();
  selectGroup(group, button.dataset.permissionGroupSelect === "all");
}

function onSubmit(event: Event): void {
  const form = event.target as HTMLFormElement | null;
  const message = form?.dataset?.confirm;

  if (message && !window.confirm(message)) {
    event.preventDefault();
    event.stopImmediatePropagation();
  }
}

export function init(root: Document = document): void {
  root.addEventListener("click", onClick);
  root.addEventListener("submit", onSubmit, true);
}

if (typeof document !== "undefined" && !(window as { __javaabuPermissionsRoles?: boolean }).__javaabuPermissionsRoles) {
  (window as { __javaabuPermissionsRoles?: boolean }).__javaabuPermissionsRoles = true;
  init();
}
