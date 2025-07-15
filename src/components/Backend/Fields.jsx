import ContentType from "./ContentType";
import BackendAuthentication from "./Authentication";
import FieldWrapper from "../FieldWrapper";

const { TextControl } = wp.components;
const { __ } = wp.i18n;

export default function BackendFields({ state, setState, errors }) {
  return (
    <>
      <FieldWrapper>
        <TextControl
          label={__("Name", "forms-bridge")}
          help={
            errors.name && __("This name is already in use", "forms-bridge")
          }
          value={state.name}
          onChange={(name) => setState({ ...state, name })}
          __nextHasNoMarginBottom
          __next40pxDefaultSize
        />
      </FieldWrapper>
      <FieldWrapper>
        <TextControl
          label={__("Base URL", "forms-bridge")}
          help={errors.base_url && __("Invalid base URL", "forms-bridge")}
          value={state.base_url}
          onChange={(base_url) => setState({ ...state, base_url })}
          __nextHasNoMarginBottom
          __next40pxDefaultSize
        />
      </FieldWrapper>
      <ContentType
        headers={state.headers}
        setHeaders={(headers) => setState({ ...state, headers })}
      />
      <BackendAuthentication
        data={state.authentication}
        setData={(authentication) => setState({ ...state, authentication })}
      />
    </>
  );
}
