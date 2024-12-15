// vendor
import React from "react";
import {
  Card,
  CardHeader,
  CardBody,
  TabPanel,
  __experimentalHeading as Heading,
  Button,
  __experimentalSpacer as Spacer,
} from "@wordpress/components";
import { useState, useEffect } from "@wordpress/element";

// source
import StoreProvider, { useStoreSubmit } from "../providers/Store";
import SettingsProvider from "../providers/Settings";
import FormsProvider from "../providers/Forms";
import GeneralSettings from "./tabs/General";
import RestApiSettings from "./tabs/RestApi";

const defaultTabs = [
  {
    name: "general",
    title: "General",
  },
  {
    name: "rest-api",
    title: "REST API",
  },
];

function Content({ tab, children }) {
  const __ = wp.i18n.__;

  const content = (() => {
    switch (tab.name) {
      case "general":
        return <GeneralSettings />;
      case "rest-api":
        return <RestApiSettings />;
      default:
        const root = (
          <div className="root" style={{ minHeight: "300px" }}></div>
        );
        setTimeout(() => wpfb.emit("tab", tab.name));
        return root;
    }
  })();

  return (
    <div id={tab.name}>
      <Card size="large" style={{ height: "fit-content" }}>
        <CardHeader>
          <Heading level={3}>{__(tab.title, "forms-bridge")}</Heading>
          <img className="addon-logo" />
        </CardHeader>
        <CardBody>
          {content}
          <Spacer paddingY="calc(16px)" />
          {children}
        </CardBody>
      </Card>
    </div>
  );
}

function SaveButton({ loading }) {
  const __ = wp.i18n.__;
  const submit = useStoreSubmit();

  const [error, setError] = useState(false);

  const onClick = () => submit().catch(() => setError(true));

  return (
    <Button
      variant={error ? "secondary" : "primary"}
      onClick={onClick}
      style={{ minWidth: "130px", justifyContent: "center" }}
      disabled={loading}
      __next40pxDefaultSize
    >
      {(error && __("Error", "forms-bridge")) || __("Save", "forms-bridge")}
    </Button>
  );
}

export default function SettingsPage({ addons }) {
  const __ = wp.i18n.__;

  const [loading, setLoading] = useState(false);

  const tabs = defaultTabs.concat(
    Object.keys(addons).map((addon) => ({
      name: addon,
      title: addons[addon],
    }))
  );

  // redirect to google sheets if comes from oauth redirection
  useEffect(() => {
    const query = new URLSearchParams(window.location.search);
    const oauthRedirection = query.has("code") && query.has("scope");
    if (oauthRedirection) {
      setTimeout(() => {
        document.querySelector("#tab-panel-0-google-sheets-api")?.click();
      }, 500);
    }
  }, []);

  return (
    <StoreProvider setLoading={setLoading}>
      <Heading level={1}>Forms Bridge</Heading>
      <TabPanel
        initialTabName="general"
        tabs={tabs.map(({ name, title }) => ({
          name,
          title: __(title, "forms-bridge"),
        }))}
      >
        {(tab) => (
          <FormsProvider>
            <SettingsProvider handle={["general", "rest-api"]}>
              <Spacer />
              <Content tab={tab}>
                <SaveButton loading={loading} />
              </Content>
            </SettingsProvider>
          </FormsProvider>
        )}
      </TabPanel>
      <Spacer show={loading} />
    </StoreProvider>
  );
}
