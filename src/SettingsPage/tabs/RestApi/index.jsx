// vendor
import React from "react";
import { __ } from "@wordpress/i18n";
import {
  Card,
  CardHeader,
  CardBody,
  __experimentalHeading as Heading,
  PanelRow,
} from "@wordpress/components";

// source
import FormHooks from "../../../components/FormHooks";
import RestFormHook from "./FormHook";
import useRestApi from "./useRestApi";

export default function RestApiSetting() {
  const __ = wp.i18n.__;
  const [{ form_hooks: hooks }, save] = useRestApi();

  return (
    <Card size="large" style={{ height: "fit-content" }}>
      <CardHeader>
        <Heading level={3}>{__("REST API", "forms-bridge")}</Heading>
      </CardHeader>
      <CardBody>
        <PanelRow>
          <FormHooks
            hooks={hooks}
            setHooks={(form_hooks) => save({ form_hooks })}
            FormHook={RestFormHook}
          />
        </PanelRow>
      </CardBody>
    </Card>
  );
}
