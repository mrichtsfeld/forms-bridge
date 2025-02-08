// source
import FormHooks from "../../../../src/components/FormHooks";
import FinanCoopFormHook from "./FormHook";
import useFinanCoopApi from "../hooks/useFinanCoopApi";

const { PanelRow } = wp.components;

export default function FinancoopSetting() {
  const [{ form_hooks: hooks }, save] = useFinanCoopApi();

  const update = (field) => save({ form_hooks: hooks, ...field });

  return (
    <PanelRow>
      <FormHooks
        hooks={hooks}
        setHooks={(form_hooks) => update({ form_hooks })}
        FormHook={FinanCoopFormHook}
      />
    </PanelRow>
  );
}
