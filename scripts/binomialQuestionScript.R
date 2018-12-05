suppressMessages(library(caret))
library(rjson)
args <- commandArgs(TRUE)
table <- paste(fromJSON(file="config.json", method="C")$DATA_DIR, "/", args[3], "/binomialQuestion/binomialQuestionData_", args[1], "_", args[2], ".txt", sep="")
formula <- "result~"

for (i in seq_along(args)) {
    if (i > 3) {
        formula <- paste(formula, args[i], sep="+")
    }
}

formula <- as.formula(formula)
questionsData <- read.table(table, sep=",", header=TRUE)
questionsData$result <- as.factor(questionsData$result)

ctrl <- trainControl(method="repeatedcv", number=2, repeats=5)
mod_fit <- train(formula, data=questionsData, method="glm", family="binomial", trControl=ctrl)

mod_fit$results
summary(mod_fit)