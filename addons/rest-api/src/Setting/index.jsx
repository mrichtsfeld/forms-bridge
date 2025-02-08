// source
import FormHooks from "../../../../src/components/FormHooks";
import RestFormHook from "./FormHook";
import useRestApi from "../hooks/useRestApi";

const { PanelRow } = wp.components;

export default function RestSetting() {
  const [{ form_hooks: hooks }, save] = useRestApi();

  const update = (field) => save({ form_hooks: hooks, ...field });

  return (
    <PanelRow>
      <FormHooks
        hooks={hooks}
        setHooks={(form_hooks) => update({ form_hooks })}
        FormHook={RestFormHook}
      />
    </PanelRow>
  );
}
